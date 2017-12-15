<?php

namespace Rutorika\Console;

trait ConsoleTrait
{
    /**
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function users()
    {
        $class = $this->getUserClassname();

        return $class::select();
    }

    /**
     *
     * @return text
     */
    protected function getUserClassname()
    {
        $param = 'rutorika.console.user_classname';
        $value = config()->get($param);

        if (empty($value)) {
            $msg = sprintf('The parameter "%s" was not found in the configuration. Try it now "php artisan config:clear".', $param);
            throw new ConsoleException($msg);
        }

        if (!class_exists($value)) {
            $msg = sprintf('Missing User Model Class "%s". Check the configuration parameter "%s"', $value, $param);
            throw new ConsoleException($msg);
        }

        return $value;
    }

    /**
     *
     * @return text
     */
    protected function getModelNamespace()
    {
        $param = 'rutorika.console.model_namespace';
        $value = config()->get($param);

        if (empty($value)) {
            $msg = sprintf('The parameter "%s" was not found in the configuration. Try it now "php artisan config:clear".', $param);
            throw new ConsoleException($msg);
        }

        if (!preg_match('/^[a-z\d_\\\]{1,50}$/i', $value)) {
            $msg = sprintf('Incorect configuration parameter "%s" value "%s"', $param, $value);
            throw new ConsoleException($msg);
        }

        return preg_replace(['/^\\\+/', '/\\\+$/'], ['', ''], $value);
    }
}