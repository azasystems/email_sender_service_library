<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSchemas extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo $this->execute('create schema objects');
        echo $this->execute('create schema operations');
        echo ' Создали схемы БД';
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        echo $this->execute('drop schema operations');
        echo $this->execute('drop schema objects');
        echo ' Удалили схемы БД';
    }
}
