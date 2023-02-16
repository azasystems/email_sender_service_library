# Установка системы:
## В командной строке требуется выполнить скрипт для установки:

$ install.sh

Скриптом выполняются установки:

### 0. Предварительная настройка среды (файл .env) с переменными по умолчанию (файл .env.example).

Сервис считывает настройки из файла .env

Для удобства есть настройки по умолчанию в файле .env.example

Инсталлятором копируются настройки .env из дефолтных:

$ cp -n .env.example .env

При инсталляции существующий .env (если он уже есть) не перезапишется из дефолтных настроек,
так как указана опция -n, означающая "никогда не перезаписывать существующие файлы".

Для тонкого тюнинга сервиса далее выполняется запуск редактора конфигурации: 

$ nano .env 

Так мы настроим нужные параметры в .env. Закрыв редактор, запускается основная установка.

### 1. Установка зависимых библиотек в папку /vendor и создание composer.lock через composer install:

$ composer i

[Детали о командах композера](https://phpprofi.ru/blogs/post/52)

В клиенте этой библиотеки при разработке для обновления можно вызывать:

$ composer upgrade

Скрипты .sh лучше реализовать внутри composer.json в "scripts": {},
но в этом проекте сделал как файлы .sh, для наглядности и удобства редактирования.

Следите, чтобы установка была вся зеленая.

## 2. Docker - вызвать docker-compose для поднятия докер-контейнеров с режимом выхода в шелл:

$ ./docker-run.sh

В реализации применяются Docker для запуска контейнеров:

Docker Compose configuration to run PHP-FPM, Nginx, PostgreSQL and Composer:

[Описание на английском](docker.md)

- Kubernetes

Можно на будущее запустить это в кластере Kubernetes, и эта работа выходит за рамки ТЗ:
[Как запускать различные контейнеры в Kubernetes](https://serveradmin.ru/nastroyka-kubernetes/)


### 3. Миграции

На БД Postgres SQL создать базу, схемы, таблицы и процедуры.

Для создания БД требуется накатить миграции (все сразу) командой:
$ ./migrate_up.sh

Для отката создания БД требуется откатить миграции (несколько раз, по одной миграции за раз) командой ./migrate_down.sh

Сделаны через [phinx](https://book.cakephp.org/phinx/0/en/install.html)

## 4. Использование php-cs-fixer для фикса стандартов кодирования:

$ ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

[php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)

## 5. Использование psalm для контроля качества кода:

$ ./vendor/bin/psalm --show-info=true

[psalm docs](https://psalm.dev/docs/)

Запуск psalm, php-cs-fixer, codeception tests одновременно при разработке:

$ ./php_quality.sh

## 6. Документация кода в phpDocumentor

Описание [phpDocumentor](https://docs.phpdoc.org/3.0/guide/getting-started/installing.html)

Документация из кода проекта собирается в папке [.phpdoc/build](../.phpdoc/build) командой:

$ phpdoc-buld.sh

Она вызывает:

$ docker run --rm -v ${PWD}:/data phpdoc/phpdoc:3

[Открыть свежую документацию кода в браузере](../.phpdoc/build/index.html)

## 7. Для тестирования накатить скрипт tester для наполнения 1 М пользователей

Для этого использовать вызов http://localhost/script.php для вызова своих скриптов, или шелл-команду с параметрами.

Вызов на сайте: script.php [parameters1]

Вызов шелл-команды: script.php?parameters2

Где возможные значения [parameters1]: --help --service=tester --service=validator --service=sender --service=deadletter --service=truncate 

Где возможные значения [parameters2]: ?help&service=tester&service=validator&service=sender&service=deadletter&service=truncate

# Дополнительные инструменты

## Модульные тесты (Unit tests)

[Сделаны через codeception](https://klisl.com/codeception_installation.html)

Тесты расположены в папке [tests](../tests) 

[Пример 1](../tests/Unit/src/app/View/emailTemplateTest.php)

Запуск тестов: $ ./codecept.sh run Unit

## Запуск скриптов в заданиях по крону

[Файл crontab](../crontab)

