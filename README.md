# Laravel console

Консольные команды:

* **rutorika:user:list** - просмотр пользователей
* **rutorika:user:password** - смена пароля пользователя
* **rutorika:db-view** - список представлений базы данных
* **rutroika:db-view:update** - обновление представлений базы данных

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


## Отправка и ротация отчетов

Для отправки и ротации отчетов в crontab необходимо добавить вызовы команд:

* php artisan rutorika:report
* php artisan rutorika:report-rotate --maxlife=30




