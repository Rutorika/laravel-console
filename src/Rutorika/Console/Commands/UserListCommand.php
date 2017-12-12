<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Rutorika\Console\UsersTrait;

class UserListCommand extends Command
{
    use UsersTrait;

    protected $name = 'rutorika:user:list';

    protected $description = 'Список пользователей';

    public function handle()
    {
        $users = $this->users()->all(['id', 'name', 'email'])->toArray();

        $this->table(['ID', 'Name', 'Email'], $users);
    }
}