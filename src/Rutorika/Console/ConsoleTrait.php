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
            abort(500, "В конфигурации отсутствует параметр " . $param . ". Попробуйте выполнить php artisan config:clear.");
        }

        if (!class_exists($value)) {
            abort(500, "Отсутствует класс модели пользователя {$value}. Проверьте параметр конфигурации {$param}");
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
            abort(500, "В конфигурации отсутствует параметр " . $param . ". Попробуйте выполнить php artisan config:clear.");
        }

        if (!preg_match('/^[a-z\d_\\\]{1,50}$/i', $value)) {
            abort(500, sprintf('Некорректное значение параметра конфигурации "%s" "%s"', $param, $value));
        }

        return $value;
    }
}