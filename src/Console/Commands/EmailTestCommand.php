<?php

namespace Rutorika\Console\Commands;

use Illuminate\Console\Command;

class EmailTestCommand extends Command
{
    protected $name = 'rutorika:email:test';

    protected $description = 'Отправка тестового email';

    public function handle()
    {
        $this->line("\n");

        $fromEmail = \Config::get('mail.from.address');
        $fromName  = \Config::get('mail.from.name');

        if (empty($fromEmail)) {
            $this->error('В config/mail.php не указан email отправителя');
            $this->line("\n");
            exit;
        }

        if (empty($fromName)) {
            $this->error('В config/mail.php не указано имя отправителя');
            $this->line("\n");
            exit;
        }

        $email = $this->ask('Email получателя');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Некорректный email');
            $this->line("\n");
            exit;
        }

        \Mail::send('emails.message', array(), function($message) use($email, $fromEmail, $fromName) {

            $message->to(array($email));
            $message->subject('Test email');

            $message->replyTo($fromEmail, $fromName);
        });
    }
}
