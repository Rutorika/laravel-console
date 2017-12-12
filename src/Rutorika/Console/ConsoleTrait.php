<?php

namespace Rutorika\Console;

trait ConsoleTrait
{
    /**
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getUserModel()
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
            abort(500, sprintf('The parameter "%s" was not found in the configuration. Try it now "php artisan config:clear".', $param));
        }

        if (!class_exists($value)) {
            abort(500, sprintd('Missing User Model Class "%s". Check the configuration parameter "%s"', $value, $param));
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
            abort(500, sprintf('The parameter "%s" was not found in the configuration. Try it now "php artisan config:clear".', $param));
        }

        if (!preg_match('/^[a-z\d_\\\]{1,50}$/i', $value)) {
            abort(500, sprintf('Incorect configuration parameter "%s" value "%s"', $param, $value));
        }

        return preg_replace(['/^\\\+/', '/\\\+$/'], ['', ''], $value);
    }
}