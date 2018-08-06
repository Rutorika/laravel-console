<?php

namespace App\Commands;

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

class DbviewsUpdateCommand extends Command
{
    protected $name = 'rutorika:db-views-update';

    protected $description = 'Обновление представлений Postgresql';

    protected $signature = 'rutorika:dbviews:update {file=none}';

    public function handle()
    {
        $this->line("");

        $param = $this->getFileArgument();

        // Список миграций

        if (empty($param) OR $param == 'list') {

            $this->printMigrationList();

        // Выполнение всех миграций

        } else if ($param == 'all') {

            $files = $this->getMigrationFiles();
            foreach ($files as $file) {
                $this->migrateFile($file);
            }

        // Выполнение одной миграции

        } else {

            $this->migrateFile($param);
        }
    }

    protected function migrateFile($file)
    {
        $this->info("Migrate file: " . $file);

        $file = $this->getFilepath($file);
        $src  = file_get_contents($file);

        if (preg_match('/(^|\s+)(create|alter|drop)(\s+table)/iu', $src, $match)) {
            $this->error("Найдено выражение (create|alter|drop)");
            $this->error("Пропуск миграции");

        } else if (preg_match('/%CURRENCY_(LOWER|UPPER)_CODE%/', $src)) {
            $this->info("Migrate type: currency");
            $this->migrateCurrency($src);

        } else {
            $this->migrate($src);
        }

        $this->line("");
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

    protected function printMigrationList()
    {
        $files = $this->getMigrationFiles();

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
        $sql = str_replace('%APP_CURRENCY_LOWER_CODE%', strtolower(\Currency::app()), $src);
        $sql = str_replace('%APP_CURRENCY_UPPER_CODE%', strtoupper(\Currency::app()), $sql);
        $sql = explode(";", $sql);

        foreach ($sql as $sqlQuery) {

            if (empty($sqlQuery)) {
                continue;
            }

            $sqlQuery = trim($sqlQuery);
            \DB::statement($sqlQuery);
        }
    }

    protected function migrateCurrency($src)
    {
        $sql = explode(";", $src);

        $currencies = \Currency::all();

        foreach ($currencies as $code) {

            $upperCode = strtoupper($code);
            $lowerCode = strtolower($code);

            foreach ($sql as $sqlQuery) {

                $sqlQuery = trim($sqlQuery);
                if (empty($sqlQuery)) {
                    continue;
                }
                
                $sqlQuery = str_replace('%CURRENCY_LOWER_CODE%', $lowerCode, $sqlQuery);
                $sqlQuery = str_replace('%CURRENCY_UPPER_CODE%', $upperCode, $sqlQuery);

                \DB::select(\DB::raw($sqlQuery));

            }
        }
    }

    protected function getMigrationFiles()
    {
        $path  = app_path() . '/../database/views';
        $files = scrooge_read_dir($path, '*.sql');

        if (empty($files)) {
            $this->info("Миграции в директории {$path} не найдены");
            $this->line("");
        }

        return $files;
    }

    protected function getFilepath($file)
    {
        $file = app_path() . '/../database/views/' . $file;

        if (!file_exists($file)) {
            $this->error("Файл {$file} не найден");
            $this->line("");
            exit;
        }

        return $file;
    }
}
