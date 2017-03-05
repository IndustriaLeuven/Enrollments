<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170305164454 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $shortUrl = $schema->createTable('plugin_short_url');

        $shortUrl->addColumn('id', 'integer')->setAutoincrement(true);
        $shortUrl->addColumn('slug', 'string');
        $shortUrl->addColumn('form_id', 'guid');

        $shortUrl->setPrimaryKey(['id']);
        $shortUrl->addUniqueIndex(['slug']);
        $shortUrl->addForeignKeyConstraint($schema->getTable('form'), ['form_id'], ['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('plugin_short_url');
    }
}
