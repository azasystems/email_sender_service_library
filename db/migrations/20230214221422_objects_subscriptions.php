<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ObjectsSubscriptions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute("
SET search_path TO objects;

create table subscriptions
(
    subscription_id serial
        constraint subscriptions_pk
            primary key,
    email_id        integer not null
        constraint subscriptions_emails_email_id_fk
            references objects.emails,
    validts         timestamp default (now() + '1 mon'::interval month)
);

comment on table subscriptions is 'Подписки';

comment on column subscriptions.subscription_id is 'Id подписки';

comment on column subscriptions.email_id is 'ID электронной почты подписки';

comment on column subscriptions.validts is 'unix ts до которого действует ежемесячная подписка';

CREATE INDEX subscriptions_validts
  ON subscriptions(validts);

alter table subscriptions
    owner to postgres;
        ");
        echo ' Создали Подписки';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute("
SET search_path TO objects;

drop table subscriptions;     
        ");

        echo ' Удалили Подписки';
    }
}
