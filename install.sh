# 0. Предварительная настройка среды (файл .env) с переменными по умолчанию (файл .env.example).
cp -n .env.example .env

read -p "Начало установки. Отредактируйте параметры и запомните имя базы данных. Нажмите Enter"
nano .env

read -p "Отредактируйте имя базы данных. Нажмите Enter"
# For CREATE DATABASE
nano .docker/conf/postgres/init.sql

# 1. Установка зависимых библиотек в папку /vendor и создание composer.lock через composer install:
read -p "Начало установки. Нажмите Enter"
composer i
composer update

# 2. Docker - вызвать docker-compose для поднятия докер-контейнеров с режимом выхода в шелл, так же тут создается база данных:
./docker-stop.sh
./docker-up.sh
# Этот рестарт нужен, чтобы БД поднялась, а иначе после up она не видна.
./docker-restart.sh

read -p "Можно проверить, соединившись с базой данных, что настроенная БД создалась в PostgreSQL. Если не создалась, то соединитесь с PostgreSQL и создайте БД сами. Дальше - начало наката миграций на БД. Нажмите Enter"
# 3. Миграции
./migrate_up.sh

read -p "Установка php-cs-fixer. Нажмите Enter"
# 4. Использование php-cs-fixer для фикса стандартов кодирования:
mkdir -p tools/php-cs-fixer
composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer

read -p "Запуск проверки кода: psalm, php-cs-fixer, codeception tests. Нажмите Enter"
# 5. Использование psalm для контроля качества кода, php-cs-fixer для фикса стандартов кодирования, codeception tests для запуска тестов:
./php_quality.sh

read -p "Генерация документации кода в phpDocumentor. Нажмите Enter"
# 6. Генерация документации кода в phpDocumentor
./phpdoc-buld.sh

read -p "Для тестирования накатить скрипт tester для наполнения 1 М пользователей. Нажмите Enter"
# 7. Для тестирования накатить скрипт tester для наполнения 1 М пользователей
./tester.sh

