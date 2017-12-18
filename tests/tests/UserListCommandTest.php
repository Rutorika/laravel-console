<?php

namespace Rutorika\Console\Test;

use Rutorika\Console\Test\UserModel;
use Rutorika\Console\Commands\UserListCommand;

class UserListCommandTest extends ConsoleTestBase
{
    protected function seedUsers()
    {
        UserModel::create([
            'name' => 'First user',
            'password' => 'password',
            'email' => 'first@email.ru'
        ]);

        UserModel::create([
            'name' => 'Second user',
            'password' => 'password',
            'email' => 'second@email.ru'
        ]);
    }

    public function test_config()
    {
        $config = config()->get('rutorika.console');

        $this->assertNotEmpty($config['user_classname'], 'Missing user_classname config parameter');
    }

    public function test_command_without_options()
    {
        $this->seedUsers();

        $command = $this->runArtisanCommand(UserListCommand::class);
        $output  = $command->getDisplay();

        $this->assertContains('first@email.ru', $output);
        $this->assertContains('second@email.ru', $output);
    }
    
    public function test_command_with_email_option()
    {
        $this->seedUsers();

        $command = $this->runArtisanCommand(UserListCommand::class, ['--email' => 'first@email.ru']);
        $output  = $command->getDisplay();
        
        $this->assertContains('first@email.ru', $output);
        $this->assertNotContains('second@email.ru', $output);
    }

    public function test_command_with_like_email_option()
    {
        $this->seedUsers();

        $command = $this->runArtisanCommand(UserListCommand::class, ['--email' => 'first']);
        $output  = $command->getDisplay();
        
        $this->assertContains('first@email.ru', $output);
        $this->assertNotContains('second@email.ru', $output);
    }

    public function test_command_with_id_option()
    {
        $this->seedUsers();

        $command = $this->runArtisanCommand(UserListCommand::class, ['--id' => '1']);
        $output  = $command->getDisplay();

        $this->assertContains('first@email.ru', $output);
        $this->assertNotContains('second@email.ru', $output);
    }
}
