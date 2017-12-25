<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Rutorika\Console\ConsoleTrait;

class MakeModelCommand extends Command
{
    use ConsoleTrait;

    protected $name = 'rutorika:make-model';

    protected $description = 'Create model file from database table';

    protected $useSortable = false;

    protected $sortableGroupField = null;

    protected $signature = 'rutorika:make-model {--T|table= : Database table} {--R|rewrite : Rewrite existing model file}';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $table = $this->getTableOption();

        if (empty($table)) {
            $table = $this->getTableName();
        }

        $columns = $this->getTableColumns($table);

        $file = $this->getModelFile($table);

        $this->setUseSortable($columns);

        $source = $this->makeModel($table, $columns);

        $this->writeModel($file, $source);

        $this->line("Model {$file} was created");

        if ($this->useSortable) {
            $this->line("\n");
            $this->line("Warning!");
            $this->line("The model has sortable. Add App\\Models\\" . studly_case($table) . "::class to config/sortable.php");
        }

        $this->line("\n");
    }

    protected function getTableOption()
    {
        $value = $this->option('table');

        if (!empty($value) && preg_match('/^[a-z]{1}[a-z\d_]+$/i', $value)) {
            return strtolower($value);
        }

        return null;
    }

    protected function getRewriteOption()
    {
        return (bool) $this->option('rewrite');
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

    protected function writeModel($file, $source)
    {
        $p = pathinfo($file);

        if (!file_exists($p['dirname'])) {
            \File::makeDirectory($p['dirname']);
        }

        file_put_contents($file, $source);
    }

    protected function getTableColumns($table)
    {
        $columns = Schema::getColumnListing($table);

        if (empty($columns)) {
            $this->error("Table {$table} not found or empty");
            $this->line("\n");
            exit;
        }

        $response = array();

        foreach($columns as $row) {
            $response[] = array(
                'name' => $row,
                'type' => \DB::connection()->getDoctrineColumn($table, $row)->getType()->getName()
            );
        }
        
        return $response;
    }

    // Невозможно получить мета на пустой таблице

//    protected function getTableColumns($table)
//    {
//        $query = \DB::connection()->getPdo()->query("SELECT * FROM " . $table . " limit 0");
//        $count = $query->columnCount();
//
//        $result = [];
//
//        for ($i = 0; $i < $count; $i++) {
//            $m = $query->getColumnMeta($i);
//            $result[] = [
//                'name' => $m['name'],
//                'type' => $m['native_type']
//            ];
//        }
//
//        dd($result);
//
//        return $result;
//    }

    protected function setUseSortable($columns)
    {
        foreach ($columns as $row) {
            if ($row['name'] == 'position') {

                if (!class_exists('Rutorika\\Sortable\\SortableServiceProvider')) {
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

    protected function getModelFile($table)
    {
        $namespace = $this->getModelNamespace();
        $namespace = preg_replace('/^\\\?App/', '', $namespace);
        $namespace = preg_replace('/^\\\+/', '', $namespace);

        $dir = str_replace('\\', '/', $namespace);
        $dir = app_path($dir);

        $file = $dir . '/' . studly_case($table) . '.php';

        if ($this->getRewriteOption() === true) {
            return $file;
        }

        if (file_exists($file)) {
            $table = studly_case($table);
            $result = $this->choice("Model " . $table . ".php was found. Overwrite?", array('n' => 'No', 'y' => 'Yes'), 'n', 3);
            if ($result != 'y') {
                exit;
            }
        }

        return $file;
    }

    protected function makeModel($table, $columns)
    {
        $modelName = studly_case($table);
        $namespace = $this->getModelNamespace();

        $php  = "<?php\n\n";
        $php .= "namespace {$namespace};\n\n";

        if ($this->useSortable === true) {
            $php .= "use Rutorika\\Sortable\\SortableTrait;\n\n";
        }

        $php .= $this->makeHeaderSection($table, $columns);
        $php .= "class {$modelName} extends \Eloquent\n";
        $php .= "{\n";

        if ($this->useSortable === true) {
            $php .= "    use SortableTrait;\n\n";
        }

        $php .= "    protected \$table = '{$table}';\n";
        $php .= $this->makeFillableSection($columns);
        $php .= $this->makeGuardedSection($columns);
        $php .= $this->makeTimestampsSection($columns);

        if ($this->useSortable === true AND $this->sortableGroupField !== null) {
            $php .= "\n";
            $php .= "    protected static \$sortableGroupField = '{$this->sortableGroupField}';\n";
        }

        $php .= "\n";
        $php .= "}\n";

        return $php;
    }

    protected function makeHeaderSection($table, $columns)
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

    protected function makeFillableSection($columns)
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

    protected function makeGuardedSection($columns)
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

    protected function makeTimestampsSection($columns)
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
