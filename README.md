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

Зависимости:

* **php >=7.0.0**
* **php-sqlite >= 3**
* **doctrine/dbal**
* **laravel >= 5.5**


## Разработка

```
"repositories": [
    {
        "type": "path",
        "url": "/srv/www/rutorika-console"
    }
]

composer require rutorika/laravel-console:dev-master --prefer-source

composer dump-autoload
```

## Установка

Добавить в **composer.json** репозиторий пакета:

```
"repositories": [
      {
        "type": "vcs",
        "url": "git@bitbucket.org:rutorika/laravel-console.git"
      }
]
```

Выполнить команды:

```
composer require rutorika/laravel-console:dev-master
php artisan vendor:publish --provider="Rutorika\Console\ConsoleServiceProvider"
php artisan config:clear
php artisan config:cache
```

В файлe конфигурации rutorika/console.php настроить параметр:

* **user_classname** - класс модели пользователей





