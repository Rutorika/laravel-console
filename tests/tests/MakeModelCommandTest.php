<?php

namespace Rutorika\Console\Test;

use Rutorika\Console\Commands\MakeModelCommand;

class MakeModelCommandTest extends ConsoleTestBase
{
    public function test_config()
    {
        $c = config()->get('rutorika.console');

        $namespace = empty($c['model_namespace']) ? null : $c['model_namespace'];

        $this->assertNotEmpty($namespace, 'Missing model_namespace config parameter');
    }

    public function test_command_with_options_table_and_rewrite()
    {
        $this->cleanModelDir();

        $command = $this->runArtisanCommand(MakeModelCommand::class, ['--table' => 'users', '--rewrite' => '1']);
        $output  = $command->getDisplay();

        $file = $this->getModelDir() . '/Users.php';

        $this->assertFileExists($file);

        $data = file_get_contents($file);
        $this->assertEquals($this->itIsModel($data), true);
    }

    protected function getModelDir()
    {
        return app_path('Models');
    }

    protected function cleanModelDir()
    {
        $dir = $this->getModelDir();

        if (!file_exists($dir)) {
            \File::makeDirectory($dir);
            return;
        }

//        \File::deleteDirectory($dir);
//        \File::makeDirectory($dir);

//        $file = new \Symfony\Component\Finder\SplFileInfo();
//        $file->getPath();
//        $file->getPathname();
//        $file->getPathInfo();

        $files = \File::allFiles($dir);
        foreach ($files as $file) {
            $data = file_get_contents($file->getPathname());
            if ($this->itIsModel($data)) {
                \File::delete($file->getPathname());
            }
        }
    }

    protected function itIsModel($content)
    {
        if (preg_match('/class\s+[a-zA-Z0-9]+\s+extends/s', $content) &&
            preg_match('/protected\s*\$table\s*=/', $content))
        {
            return true;
        }

        return false;
    }
}
