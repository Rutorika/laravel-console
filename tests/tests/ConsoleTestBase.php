<?php

namespace Rutorika\Console\Test;

use Orchestra\Testbench\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Rutorika\Console\ConsoleServiceProvider;

class ConsoleTestBase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--path' => 'migrations',
        ]);

//        $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
//        foreach ($tables as $table) {
//            foreach ($table as $key => $value)
//                echo $value . "\n";
//        }
    }

    public function tearDown()
    {
        $this->artisan('migrate:rollback', [
            '--database' => 'testbench'
        ]);

        parent::tearDown();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/../../src';

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('rutorika.console', 
            include __DIR__ . '/../config.php'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ConsoleServiceProvider::class
        ];
    }

    /**
     * @param $class Command class to be called.
     * @param array $options
     * @return CommandTester
     */
    public function runArtisanCommand($class, $options = [])
    {
        $command = $this->app->make($class);
        $command->setLaravel($this->app->getInstance());

        $tester = new CommandTester($command);
        $tester->execute($options);

        return $tester;
    }
}
