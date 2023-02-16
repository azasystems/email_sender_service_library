<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OperationsDeadletter extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO operations;

create table deadletter
(
    deadletter_id serial
        constraint deadletter_pk
            primary key,
    subscription_id integer not null
        constraint deadletter_subscriptions_subscription_id_fk
            references objects.subscriptions,
    try_count       integer   default 0,
    updated_at      timestamp default CURRENT_TIMESTAMP
);

comment on table deadletter is 'Недоставленные отправки';

comment on column deadletter.deadletter_id is 'ID недоставленной отправки';

comment on column deadletter.subscription_id is 'ID недоставленной подписки';

comment on column deadletter.try_count is 'Счетчик попыток переотправки';

comment on column deadletter.updated_at is 'Дата и время попытки отправки';

alter table deadletter
    owner to postgres;

create unique index deadletter_subscription_id_index
    on deadletter (subscription_id);
        ");
        echo ' Создали Недоставленные отправки';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO operations;

drop table deadletter;
        ");

        echo ' Удалили Недоставленные отправки';
    }
}
