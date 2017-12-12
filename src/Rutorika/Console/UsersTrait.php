<?php

namespace Rutorika\Console;

trait UsersTrait
{
    /**
     * 
     * @return \Eloquent
     */
    public function users()
    {
        $param = 'rutorika.console.user_classname';

        $model = config()->get($param);

        if (empty($model)) {
            abort(500, "В конфигурации отсутствует параметр " . $param);
        }

        if (!class_exists($model)) {
            abort(500, "Отсутствует класс модели пользователя {$model}. Проверьте параметр конфигурации {$param}");
        }

        return $model::select();
    }
}