<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OperationsSender extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO operations;

create table sender
(
    sender_id serial
        constraint sender_pk
            primary key,
    subscription_id integer not null
        constraint sender_subscriptions_subscription_id_fk
            references objects.subscriptions,
    send_at      timestamp
);

comment on table sender is 'Отправка почты';

comment on column sender.sender_id is 'ID отправки почты';

comment on column sender.subscription_id is 'ID подписки';

comment on column sender.send_at is 'Дата и время отправки почты';

alter table sender
    owner to postgres;

create unique index sender_subscription_id_uindex
    on sender (subscription_id);
        ");
        echo ' Создали Отправка почты';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO operations;

drop table sender;
        ");

        echo ' Удалили Отправка почты';
    }
}
