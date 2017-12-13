<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Rutorika\Console\ConsoleTrait;

class UserListCommand extends Command
{
    use ConsoleTrait;

    protected $name = 'rutorika:user:list';

    protected $description = 'Find users';

    protected $signature = 'rutorika:user:list {--E|email= : User email or part of email} {--I|id= : User ID}';

    protected $fields = [
        'id',
        'email'
    ];

    public function handle()
    {
        $select = $this->users()->select($this->fields);

        $email = $this->getEmailOption();

        if (!empty($email)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $select->where('email', $email);
            } else {
                $select->whereRaw('email ilike ?', [$email . '%']);
            }
        }

        $id = $this->getIdOption();

        if (!empty($id)) {
            $select->where('id', $id);
        }

        $result = $select->get()->toArray();

        $this->table($this->fields, $result);
    }

    protected function getEmailOption()
    {
        $value = $this->option('email');

        if (!empty($value) && preg_match('/^[a-z\d\._@-]+$/i', $value)) {
            return $value;
        }

        return null;
    }

    protected function getIdOption()
    {
        return (int) $this->option('id');
    }
}