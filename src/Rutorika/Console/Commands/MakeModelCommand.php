<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Rutorika\Console\ConsoleTrait;

class MakeModelCommand extends Command
{
    use ConsoleTrait;

    protected $name = 'rutorika:make-model';

    protected $description = 'Создание модели';

    protected $useSortable = false;

    protected $sortableGroupField = null;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $table = $this->getTableName();

        $columns = $this->getColumns($table);

        $file = $this->getModelFile($table);

        $this->setUseSortable($columns);

        $source = $this->makeModel($table, $columns);

        file_put_contents($file, $source);

        $this->line("Model {$file} was created");

        if ($this->useSortable) {
            $this->line("\n");
            $this->line("Warning!");
            $this->line("The model has sortable. Add App\\Models\\" . studly_case($table) . "::class to config/sortable.php");
        }

        $this->line("\n");
    }

    protected function getTableName()
    {
        $table = $this->ask('SQL table');
        $table = trim($table);

        if (!preg_match('/^[a-z0-9_]+$/i', $table)) {
            $this->error("Incorrect table name");
            $this->line("\n");
            exit;
        }

        return $table;
    }

    protected function setUseSortable($columns)
    {
        foreach ($columns as $row) {
            if ($row['name'] == 'position') {

                if (!class_exists('Rutorika\\Sortable\\SortableTrait')) {
                    $this->error("Found field \"position\" but not found rutorika-sortable package. Skip enabled sortable");
                    return;
                }

                $result = $this->choice("Found field \"position\". Enable Rutorika Sortable?", array('n' => 'No', 'y' => 'Yes'), 'y', 3);

                if ($result == 'y') {
                    $this->useSortable = true;
                    $this->setSortableGroupField($columns);
                }
            }
        }
    }

    protected function setSortableGroupField($columns)
    {
        $result = $this->choice("Use sortable group?", array('n' => 'No', 'y' => 'Yes'), 'n', 3);

        if ($result != 'y') {
            return;
        }

        $field = $this->ask("Enter group field");
        $field = trim($field);
        $field = snake_case($field);

        foreach ($columns as $row) {
            if ($row['name'] == $field) {
                $this->sortableGroupField = $field;
                return;
            }
        }

        $this->error("Field {$field} was not found");
        $this->line("\n");

        $this->setSortableGroupField($columns);
    }

    protected function getColumns($table)
    {
        $query = \DB::connection()->getPdo()->query("SELECT * FROM " . $table . " limit 1");
        $count = $query->columnCount();

        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $m = $query->getColumnMeta($i);
            $result[] = [
                'name' => $m['name'],
                'type' => $m['native_type']
            ];
        }

        return $result;
    }

    protected function getModelFile($table)
    {
        $namespace = $this->getModelNamespace();

        $file = app_path('Models') . '/' . studly_case($table) . '.php';

        if (file_exists($file)) {

            $result = $this->choice("Model " . studly_case($table) . ".php was found. Overwrite?", array('n' => 'No', 'y' => 'Yes'), 'n', 3);

            if ($result != 'y') {
                exit;
            }
        }

        return $file;
    }

    protected function makeModel($table, $columns)
    {
        $modelName = studly_case($table);

        $php  = "<?php\n\n";
        $php .= "namespace App\Models;\n\n";

        if ($this->useSortable === true) {
            $php .= "use Rutorika\\Sortable\\SortableTrait;\n\n";
        }

        $php .= $this->makeModelHeader($table, $columns);
        $php .= "class {$modelName} extends \Eloquent\n";
        $php .= "{\n";

        if ($this->useSortable === true) {
            $php .= "    use SortableTrait;\n\n";
        }

        $php .= "    protected \$table = '{$table}';\n";
        $php .= $this->makeModelFillable($columns);
        $php .= $this->makeModelGuarded($columns);
        $php .= $this->makeModelTimestamps($columns);

        if ($this->useSortable === true AND $this->sortableGroupField !== null) {
            $php .= "\n";
            $php .= "    protected static \$sortableGroupField = '{$this->sortableGroupField}';\n";
        }

        $php .= "\n";
        $php .= "}\n";

        return $php;
    }

    protected function makeModelHeader($table, $columns)
    {
        $modelName = studly_case($table);

        $php  = "/**\n";
        $php .= " * {$modelName}\n";
        $php .= " *\n";

        foreach ($columns as $row) {

            if (preg_match('/^(created_at|updated_at)$/', $row['name'])) {
                $row['type'] = '\\Carbon\\Carbon';
            }

            $php .= sprintf(" * @property %-9s $%s",   $row['type'], $row['name']) . "\n";
        }

        $php .= " */\n";

        return $php;
    }

    protected function makeModelFillable($columns)
    {
        $php  = "\n";
        $php .= "    protected \$fillable = [\n";

        foreach ($columns as $row) {
            if (!preg_match('/^(id|created_at|updated_at)$/', $row['name'])) {
                $php .= "        '{$row['name']}',\n";
            }
        }

        $php .= "    ];\n";

        return $php;
    }

    protected function makeModelGuarded($columns)
    {
        $php  = "\n";
        $php .= "    protected \$guarded = [";

        foreach ($columns as $row) {
            if ($row['name'] == 'id') {
                $php .= "'id'";
            }
        }

        $php .= "];";
        $php .= "\n";

        return $php;
    }

    protected function makeModelTimestamps($columns)
    {
        $timestamps = false;

        foreach ($columns as $row) {
            if (preg_match('/^(created_at|updated_at)$/', $row['name'])) {
                $timestamps = true;
            }
        }

        if ($timestamps === false) {
            $php  = "\n";
            $php .= "    public \$timestamps = false;\n";
        } else {
            $php = null;
        }

        return $php;
    }
}
