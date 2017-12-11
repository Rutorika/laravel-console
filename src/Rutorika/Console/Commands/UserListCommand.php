<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UserListCommand extends Command
{
    protected $name = 'rutorika:user:list';

    protected $description = 'Show users';

    public function handle()
    {
        $users = User::all(['id', 'name', 'email'])->toArray();
        $this->table(['ID', 'Name', 'Email'], $users);
    }
}