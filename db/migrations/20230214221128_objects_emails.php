<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsEmails extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create table emails
(
    email_id serial
        constraint emails_pk
            primary key,
    email    varchar not null,
    user_id  integer not null
        constraint emails_users_user_id_fk
            references objects.users
);

comment on table emails is 'Электронные почты';

comment on column emails.email_id is 'ID электронной почты';

comment on column emails.email is 'Адрес электронной почты';

comment on column emails.user_id is 'ID пользователя';

alter table emails
    owner to postgres;

create unique index emails_email_index
    on emails (email);
        ");
        echo ' Создали Электронные почты';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop table emails;       
        ");

        echo ' Удалили Электронные почты';
    }
}
