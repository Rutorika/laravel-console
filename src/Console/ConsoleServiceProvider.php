<?php

namespace Rutorika\Console;

class ConsoleServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config.php' => config_path('rutorika/console.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands(
                Commands\UserListCommand::class,
                Commands\UserPasswordCommand::class,
                Commands\ModelCommand::class,
                Commands\EmailTestCommand::class
            );
        }
    }

    public function provides()
    {
        return [];
    }
}