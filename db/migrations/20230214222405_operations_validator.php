<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OperationsValidator extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO operations;

create table validator
(
    validator_id serial
        constraint validator_pk
            primary key,
    email_id     integer not null
        constraint validator_emails_email_id_fk
            references objects.emails,
    action_id    smallint
        constraint validator_actions_action_id_fk
            references actions,
    updated_at   timestamp default CURRENT_TIMESTAMP
);

comment on table validator is 'Валидация электронных почт';

comment on column validator.validator_id is 'ID валидации';

comment on column validator.email_id is 'ID электронной почты';

comment on column validator.action_id is 'Текущее действие по проверке и валидации почты';

comment on column validator.updated_at is 'Дата и время обновления записи';

alter table validator
    owner to postgres;

create unique index validator_email_id_index
    on validator (email_id);

create index validator_action_id_updated_at_index
    on validator (action_id, updated_at);
        ");
        echo ' Создали Валидация';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO operations;

drop table validator;
        ");

        echo ' Удалили Валидация';
    }
}
