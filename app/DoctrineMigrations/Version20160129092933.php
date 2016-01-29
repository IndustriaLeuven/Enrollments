<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160129092933 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $form = $schema->createTable('form');
        $form->addColumn('id', 'guid');
        $form->addColumn('name', 'string');
        $form->addColumn('plugin_data', 'array');

        $form->setPrimaryKey(array('id'));

        $enrollment = $schema->createTable('enrollment');
        $enrollment->addColumn('id', 'guid');
        $enrollment->addColumn('data', 'json_array');
        $enrollment->addColumn('plugin_data', 'array');
        $enrollment->addColumn('timestamp', 'datetime');
        $enrollment->addColumn('form_id', 'guid');

        $enrollment->setPrimaryKey(array('id'));
        $enrollment->addForeignKeyConstraint($form, array('form_id'), array('id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('enrollment');
        $schema->dropTable('form');
    }
}
