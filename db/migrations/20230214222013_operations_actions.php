<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OperationsActions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO operations;

create table actions
(
    action_id serial
        constraint actions_pk
            primary key,
    action_name varchar not null
);

comment on table actions is 'Действия пользователя и валидации по подтверждению, проверке, не валидности, валидности, повторной валидации почты';

comment on column actions.action_id is 'ID действия';

comment on column actions.action_name is 'Имя действия';

alter table actions
    owner to postgres;

INSERT INTO operations.actions (action_id, action_name) VALUES (0, 'Почту пользователь не подтвердил по ссылке');
INSERT INTO operations.actions (action_id, action_name) VALUES (1, 'Почта не проверена и валидность не ясна');
INSERT INTO operations.actions (action_id, action_name) VALUES (2, 'Почта проверена и невалидна');
INSERT INTO operations.actions (action_id, action_name) VALUES (3, 'Почта проверена и валидна');
INSERT INTO operations.actions (action_id, action_name) VALUES (4, 'Почта проверена и валидна, но уже истек срок валидности почты, нужна повторная валидация');
        ");
        echo ' Создали Действия пользователя';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO operations;

drop table actions;     
        ");

        echo ' Удалили Действия пользователя';
    }
}
