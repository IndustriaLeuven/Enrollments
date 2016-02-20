<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160217135721 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $enrollmentCount = $schema->createTable('plugin_enrollment_count');

        $enrollmentCount->addColumn('form_id', 'guid');
        $enrollmentCount->addColumn('enrollment_id', 'guid');
        $enrollmentCount->addColumn('count', 'integer');

        $enrollmentCount->setPrimaryKey(array('enrollment_id'));
        $enrollmentCount->addForeignKeyConstraint($schema->getTable('form'), array('form_id'), array('id'));
        $enrollmentCount->addForeignKeyConstraint($schema->getTable('enrollment'), array('enrollment_id'), array('id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('plugin_enrollment_count');
    }
}
