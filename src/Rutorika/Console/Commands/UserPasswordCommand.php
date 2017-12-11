<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UserPasswordCommand extends Command
{
    protected $name = 'rutorika:user:password';

    protected $description = 'Reset user password';

    protected function getOptions()
    {
        return array(
            array('email',        null, InputOption::VALUE_OPTIONAL, 'Find user by email', null),
            array('new-password', null, InputOption::VALUE_OPTIONAL, 'New user password', null),
        );
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $identity = $this->option('email');

        if (empty($identity)) {
            $identity = $this->ask('Enter user ID or EMAIL');
        }

        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {

            $user = \App\Models\Users::where('email', '=', $identity)->first();

        } else {

            $identity = (int) $identity;

            if (empty($identity)) {
                $this->error("Incorrect user ID");
                $this->line("\n");
                return;
            }

            $user = \App\Models\Users::where('id', '=', $identity)->first();
        }

        if (empty($user)) {
            $this->error("User not found");
            $this->line("\n");
            return;
        }

        $password = $this->option('new-password');

        if (empty($password)) {
            $password = $this->ask('Enter password');
        }

        $user->password = \Hash::make($password);
        $user->save();

        $this->line("\n");
        $this->line('Password update sucessfull');
        $this->line("\n");
    }
}

