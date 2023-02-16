<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create table users
(
    user_id   serial
        constraint users_pk
            primary key,
    user_name varchar not null
);

comment on table users is 'Пользователи';

comment on column users.user_id is 'PK';

comment on column users.user_name is 'Имя пользователя';

alter table users
    owner to postgres;
        ");
        echo ' Создали Пользователи';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop table users;        
        ");

        echo ' Удалили Пользователи';
    }
}
