<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Команда выполняет создание или обновление представлений
 * Выполняет sql из файлов app/database/views
 *
 * Вывести список доступных представлений
 * php artisan rutorika:db-views-update list
 *
 * Обновить одно представление
 * php artisan rutorika:db-views-update view.sql
 *
 * Обновить все представления
 * php artisan rutorika:db-views-update all
 */

class DbViewsCommand extends Command
{
    protected $name = 'rutorika:db:views';

    protected $description = 'Обновление представлений Postgresql';

    protected $signature = 'rutorika:db:views {file=none}';

    public function handle()
    {
        $this->line("");

        $dir = $this->getViewsDir();
        $param = $this->getFileArgument();

        // Список миграций

        if (empty($param) OR $param == 'list') {
            $this->printMigrationList($dir);

        // Выполнение всех миграций

        } else if ($param == 'all') {
            $files = $this->getMigrationFiles($dir);
            foreach ($files as $file) {
                $this->runSqlfile("${dir}/${file}");
            }

        // Выполнение одной миграции

        } else {
            $this->runSqlfile("${dir}/${param}");
        }
    }

    protected function runSqlfile($file)
    {
        $this->info("Migrate file: " . $file);

        $src = file_get_contents($file);

        if (preg_match('/(^|\s+)(create|alter|drop)(\s+table)/iu', $src, $match)) {
            $this->error("Найдено выражение (create|alter|drop)");
            $this->error("Пропуск миграции");

        } else {
            $this->migrate($src);
        }

        $this->line("");
    }

    protected function printMigrationList($dir)
    {
        $files = $this->getMigrationFiles($dir);

        if (empty($files)) {
            return;
        }

        $this->info("Доступны миграции:");
        $this->line("");

        foreach ($files as $file) {
            $this->line($file);
        }

        $this->line("");

        $this->info("Пример команды:");
        $this->info("php artisan {$this->name} {$file}");
        $this->line("");

        $this->info("Выполнить все миграции:");
        $this->info("php artisan {$this->name} all");
        $this->line("");
    }

    protected function migrate($src)
    {
        $sql = explode(";", $src);

        foreach ($sql as $sqlQuery) {

            $sqlQuery = trim($sqlQuery);

            if (empty($sqlQuery)) {
                continue;
            }

            \DB::statement($sqlQuery);
        }
    }

    protected function getViewsDir()
    {
        $path = config()->get('rutorika.console.dbviews_path');

        if (empty($path)) {
            $this->line("");
            $this->error("В конфигурационнам файле rutorika/console.php не задан путь к директории с представлениями базы данных dbviews_path");
            $this->line("");
            exit;
        }

        if (!file_exists($path)) {
            $this->line("");
            $this->error("Отсутствует директория представлений " . $path);
            $this->line("");
            exit;
        }

        if (!is_dir($path)) {
            $this->line("");
            $this->error($path . " не директория");
            $this->line("");
            exit;
        }

        return $path;
    }

    protected function getFileArgument()
    {
        $file = $this->argument('file');

        if ($file == 'none') {
            return;
        }

        if (preg_match('/([^a-zA-Z0-9_.]+|\.\.)/', $file)) {
            $this->error("Некорректное имя файла " . $file);
            $this->line("");
            return;
        }

        return $file;
    }

    protected function getMigrationFiles($dir)
    {
        $files = $this->_readDir($dir, '*.sql');

        if (empty($files)) {
            $this->info("Миграции в директории {$dir} не найдены");
            $this->line("");
        }

        return $files;
    }

    /**
     * Копия функции Rutorika\Functions\Files::readDir()
     */
    private function _readDir($dir, $masq = false)
    {
        if (!file_exists($dir)) {
            return array();
        }

        $list = scandir($dir);
        if (empty($list)) {
            return array();
        }

        $search  = array('.',  '*');
        $replace = array('\.', '.*');
        $masq    = $masq ? str_replace($search, $replace, $masq) : '[^.]+.*';
        $masq    = '/^' . $masq . '$/u';

        $return = array();
        foreach ($list as $k => $v) {
            if (preg_match($masq, $v)) {
                $return[] = $v;
            }
        }

        return $return;
    }
}
