# Laravel console

Консольные команды:

```
rutorika:db:testing    - генерация тестовой базы
rutorika:email:test    - тестирование отправки email
rutorika:model         - генерация модели
rutorika:user:list     - просмотр пользователей
rutorika:user:password - смена пароля пользователя
rutorika:db:views      - обновление представлений Postgresql или Mysql
```

## Установка

laravel 6

```
composer require rutorika/laravel-console:0.6.0
```

laravel 5

```
composer require rutorika/laravel-console:0.5.0
```

```
php artisan vendor:publish --provider="Rutorika\Console\ConsoleServiceProvider"
php artisan config:clear
php artisan config:cache
```

В файлe конфигурации *rutorika/console.php* настроить параметры:

* **user_classname** модель пользователя
* **model_namespace** пространство имен моделей
* **dbviews_path** директория с представлениями





