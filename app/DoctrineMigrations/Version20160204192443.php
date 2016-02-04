<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160204192443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach(['form', 'enrollment'] as $tableName) {
            $table = $schema->getTable($tableName);
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime');
            $table->addColumn('deleted_at', 'datetime')->setNotnull(false);
            $table->addColumn('created_by', 'text')->setNotnull(false);
            $table->addColumn('updated_by', 'text')->setNotnull(false);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        foreach(['form', 'enrollment'] as $tableName) {
            $table = $schema->getTable($tableName);
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        }
    }
}
