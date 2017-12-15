<?php

namespace Rutorika\Console\Test;

use Rutorika\Console\Commands\MakeModelCommand;

class MakeModelCommandTest extends ConsoleTestBase
{
    public function testConfig()
    {
        $c = config()->get('rutorika.console');

        $this->assertNotEmpty($c['model_namespace'], 'Missing model_namespace config parameter');
    }

//    public function testDefaultCommand()
//    {
//        $this->artisan('rutorika:make-model', ['--table' => 'users']);
//
//        $file = $this->getModelPath('Users');
//
//        $this->seeFileWasCreated($file);
//
//        $this->removeCreatedModel('Users');
//    }

    protected function removeCreatedModel($class)
    {
        $file = $this->getModelPath($class);

        if (file_exists($file)) {
            unlink($file);
        }
    }

    protected function getModelPath($class)
    {
        return __DIR__ . '/../models' . '/' . $class . '.php';
    }
}
