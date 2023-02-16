<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsSeedTestSubscriptions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create or replace procedure seed_test_subscriptions(subscriptions_number integer)
    language plpgsql
as
$$
BEGIN
    FOR i IN 1..subscriptions_number
        LOOP
        IF i < 10000
        THEN
            INSERT INTO objects.subscriptions (email_id, validts)
            VALUES (i, (NOW() + INTERVAL '1 DAYS'));
        ELSE
            INSERT INTO objects.subscriptions (email_id, validts)
            VALUES (i, (NOW() + INTERVAL '30 DAYS'));
        END IF;
        
        END LOOP;
END;
$$;

comment on procedure seed_test_subscriptions(integer) is 'Заполнение таблицы Подписки (subscriptions) тестовыми данными';
        ");

        echo ' Создали Заполнение таблицы Подписки (subscriptions) тестовыми данными';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop procedure if exists seed_test_subscriptions(integer);
        ");

        echo ' Удалили Заполнение таблицы Подписки (subscriptions) тестовыми данными';
    }
}
