<?php

namespace Rutorika\Console\Test;

use Rutorika\Console\Test\UserModel;
use Rutorika\Console\Commands\UserListCommand;

class UserListCommandTest extends ConsoleTestBase
{
    public function test_config()
    {
        $c = config()->get('rutorika.console');
        $this->assertNotEmpty($c['user_classname'], 'Missing user_classname config parameter');
    }

//    public function test_command_without_options()
//    {
//        $command = $this->runArtisanCommand(UserListCommand::class);
//        $command->execute([]);
//
//        $output = $command->getDisplay();
//
//        $this->assertContains('email', $output);
//    }

    public function test_command_with_options()
    {
        UserModel::create([
            'name' => '1',
            'password' => '1',
            'email' => 'first@email.ru'
        ]);

        UserModel::create([
            'name' => '1',
            'password' => '1',
            'email' => 'second@email.ru'
        ]);

        $command = $this->runArtisanCommand(UserListCommand::class, ['--email' => 'first@email.ru']);
        $command->execute([]);

        $output = $command->getDisplay();
dd($output);
        $this->assertContains('first@email.ru', $output);
        $this->assertNotContains('second@email.ru', $output);
    }
}
