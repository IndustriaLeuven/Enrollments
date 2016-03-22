<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160317223422 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $uniqueField = $schema->createTable('plugin_unique_field');

        $uniqueField->addColumn('id', 'integer')->setAutoincrement(true);
        $uniqueField->addColumn('form_id', 'guid');
        $uniqueField->addColumn('enrollment_id', 'guid');
        $uniqueField->addColumn('fieldName', 'string');
        $uniqueField->addColumn('data', 'object');

        $uniqueField->setPrimaryKey(array('id'));
        $uniqueField->addUniqueIndex(array('enrollment_id', 'fieldName'));
        $uniqueField->addIndex(array('form_id', 'fieldName'));
        $uniqueField->addForeignKeyConstraint($schema->getTable('form'), array('form_id'), array('id'));
        $uniqueField->addForeignKeyConstraint($schema->getTable('enrollment'), array('enrollment_id'), array('id'));

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('plugin_unique_field');
    }
}
