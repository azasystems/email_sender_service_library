<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsSeedTestUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create or replace procedure seed_test_users(users_number integer)
    language plpgsql
as
$$
BEGIN
    FOR i IN 1..users_number
        LOOP
            INSERT INTO objects.users (user_name)
            VALUES (CONCAT(substr(md5(random()::text), 0, 5), i));
        END LOOP;
END;
$$;

comment on procedure seed_test_users(integer) is 'Заполнение таблицы пользователей (users) тестовыми данными';       
        ");

        echo ' Создали Заполнение таблицы пользователей (users) тестовыми данными';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop procedure if exists seed_test_users(integer);
        ");

        echo ' Удалили Заполнение таблицы пользователей (users) тестовыми данными';
    }
}
