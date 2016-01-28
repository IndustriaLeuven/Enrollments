<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160128212600 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $users = $schema->createTable('app_user');
        $users->addColumn('id', 'integer')->setAutoincrement(true);
        $users->addColumn('auth_id', 'string');

        $users->setPrimaryKey(array('id'));
        $users->addUniqueIndex(array('auth_id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('app_user');
    }
}
