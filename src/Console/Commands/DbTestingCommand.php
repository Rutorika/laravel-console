<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DbTestingCommand extends Command
{
    protected $name = 'rutorika:db:testing';

    protected $description = 'Пересоздание тестовой базы .env.testing на основе .env';

    protected $signature = 'rutorika:db:testing {--seed}';

    public function handle()
    {
        $this->line("");

        $origEnv = $this->envParams('.env');
        $testEnv = $this->envParams('.env.testing');

        $this->infoOperation('Environment',  $origEnv['env']);

        if ($origEnv['env'] == 'production') {
            $this->errorExit("Production окружение. Выполнение тестов остановлено.");
        }

        if ($testEnv['host'] != $origEnv['host']) {
            $this->errorExit("Хост тестовой базы {$testEnv['host']} не соответствует хосту оригинальной базы {$origEnv['host']}");
        }

        if ($testEnv['db'] == $origEnv['db']) {
            $this->error("Имя тестовой базы данных совпадает с именем оригинальной базы");
            $this->errorExit("Измените параметр DB_DATABSE в .env.testing или .env");
        }

        if (!preg_match("/_test(ing)?$/", $testEnv['db'])) {
            $this->errorExit("Имя тестовой базы данных должно иметь суффикс _test или _testing");
        }

        $this->infoOperation('Source DB',  $origEnv['host'] . "@" . $origEnv['db']);
        $this->infoOperation('Testing DB', $testEnv['host'] . "@" . $testEnv['db']);

        $this->line("");

        putenv("PGPASSWORD=" . $origEnv['pass']);

        $this->checkDbConnect($origEnv);
        $dump = $this->makeDump($origEnv);

        putenv("PGPASSWORD=" . $testEnv['pass']);

        $this->checkDbConnect($testEnv);
        $this->dropTestDb($testEnv);
        $this->createTestDb($testEnv);
        $this->createTestDbSchema($testEnv, $dump);
        $this->infoOperation("Load dictionary tables", true);

//        if ($this->option('seed')) {
//            \Artisan::call('db:seed', ['--class' => 'TestingDatabaseSeeder']);
//            $this->infoOperation("Seed basic data", true);
//        }

        $this->line("");
    }

    protected function checkDbConnect($env)
    {
        $cmd = vsprintf("psql -h %s -U %s %s -c 'SELECT NOW();'", [
            escapeshellarg($env['host']),
            escapeshellarg($env['user']),
            $env['db']
        ]);

        exec($cmd, $cmdout, $cmdresult);

        if ($cmdresult != 0) {
            $this->line("");
            exit(1);
        }
    }

    protected function dropTestDb($env)
    {
        $title = 'Drop testing DB';

        $cmd = vsprintf("psql -h %s -U %s postgres -c 'DROP DATABASE IF EXISTS %s;'", [
            escapeshellarg($env['host']),
            escapeshellarg($env['user']),
            $env['db']
        ]);

        // $this->infoOperation($title, $cmd);

        exec($cmd, $cmdout, $cmdresult);

        if ($cmdresult != 0) {
            $this->line("");
            exit(1);
        }

        $this->infoOperation($title, true);
    }

    protected function createTestDb($env)
    {
        $title = 'Create testing DB';

        $cmd = vsprintf("psql -h %s -U %s postgres -c 'CREATE DATABASE %s;'", [
            escapeshellarg($env['host']),
            escapeshellarg($env['user']),
            $env['db']
        ]);

        // $this->infoOperation($title, $cmd);

        exec($cmd, $cmdout, $cmdresult);

        if ($cmdresult != 0) {
            $this->line("");
            exit(1);
        }

        $this->infoOperation($title, true);
    }

    protected function createTestDbSchema($env, $dump)
    {
        $title = 'Load schema into testing';

        $cmd = vsprintf("psql -h %s -U %s %s < %s", [
            escapeshellarg($env['host']),
            escapeshellarg($env['user']),
            escapeshellarg($env['db']),
            $dump
        ]);

        // $this->infoOperation($title, $cmd);

        exec($cmd, $cmdout, $cmdresult);

        if ($cmdresult != 0) {
            $this->line("");
            exit(1);
        }

        $this->infoOperation($title, true);
    }

    protected function makeDump($env)
    {
        $title = 'Make source schema';

        $dir  = storage_path() . '/testing';
        $file = $dir . '/testingdb.sql';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        if (!preg_match('/^(localhost|127.0.0.1)$/', $env['host'])) {
            $this->warn("Используется нелокальная база данных {$env['host']}");
            $this->warn("Возможны проблемы с несовместимостью версий");
            $this->line("");
        }

        // Схема

        // Дамп только схемы
        // $cmd = vsprintf("pg_dump -O -s -h %s -U %s %s > %s", [

        $cmd = vsprintf("pg_dump -O -h %s -U %s %s > %s", [
            escapeshellarg($env['host']),
            escapeshellarg($env['user']),
            escapeshellarg($env['db']),
            $file
        ]);

        exec($cmd, $cmdout, $cmdresult);

        if ($cmdresult != 0) {
            $this->line("");
            exit(1);
        }

//        // Таблицы, которые необходимо перенести из продакшн окружения
//
//        $cmd = vsprintf("pg_dump -O -a -h %s -U %s -t dict1 %s >> %s", [
//            escapeshellarg($env['host']),
//            escapeshellarg($env['user']),
//            escapeshellarg($env['db']),
//            $file
//        ]);
//
//        exec($cmd, $cmdout, $cmdresult);
//
//        if ($cmdresult != 0) {
//            $this->line("");
//            exit(1);
//        }

        $this->infoOperation($title, true);

        return $file;
    }

    protected function envParams($envfile)
    {
        $envfile = app_path() . '/../' . $envfile;

        if (!file_exists($envfile)) {
            $this->errorExit("Не найден файл " . $envfile);
        }

        $data = file_get_contents($envfile);

        $r1 = preg_match("/APP_ENV=([^\s]+)/",     $data, $env);
        $r2 = preg_match("/DB_HOST=([^\s]+)/",     $data, $host);
        $r3 = preg_match("/DB_DATABASE=([^\s]+)/", $data, $db);
        $r4 = preg_match("/DB_USERNAME=([^\s]+)/", $data, $user);
        $r5 = preg_match("/DB_PASSWORD=([^\s]+)/", $data, $pass);

        if (!$r2) {
            $this->errorExit("В .env.testing отсутствует параметр DB_HOST");
        }

        if (!$r3) {
            $this->errorExit("В .env.testing отсутствует параметр DB_DATABASE");
        }

        if (!$r4) {
            $this->errorExit("В .env.testing отсутствует параметр DB_USERNAME");
        }

        if (!$r5) {
            $this->errorExit("В .env.testing отсутствует параметр DB_PASSWORD");
        }

        $params = [
            'env'  => $env[1],
            'host' => $host[1],
            'db'   => $db[1],
            'user' => $user[1],
            'pass' => $pass[1]
        ];

        return $params;
    }

    protected function infoOperation($operation, $result, $style = 'line')
    {
        if (is_bool($result)) {
            $result = $result ? 'OK' : 'NO';
        }

        $line = sprintf("%-25s : %s", $operation, $result);

        $this->info($line);
    }

    protected function errorExit($message)
    {
        $this->error($message);
        $this->line("");
        exit(1);
    }
}
