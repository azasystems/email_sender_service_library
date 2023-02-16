<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsSeedTestEmails extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create or replace procedure seed_test_emails(emails_number integer)
    language plpgsql
as
$$
BEGIN    
    FOR i IN 1..emails_number
        LOOP
            INSERT INTO objects.emails (email, user_id)
            VALUES (CONCAT(substr(md5(random()::text), 0, 5), i, '@', substr(md5(random()::text), 0, 5), '.com'), i);
        END LOOP;
END;
$$;

comment on procedure seed_test_emails(integer) is 'Заполнение таблицы электронных почт (emails) тестовыми данными';      
        ");

        echo ' Создали Заполнение таблицы электронных почт (emails) тестовыми данными';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop procedure if exists seed_test_emails(integer);
        ");

        echo ' Удалили Заполнение таблицы электронных почт (emails) тестовыми данными';
    }
}
