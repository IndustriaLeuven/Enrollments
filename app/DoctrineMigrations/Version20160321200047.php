<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160321200047 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach(['form', 'enrollment'] as $table) {
            foreach(['created_by', 'updated_by'] as $col) {
                $schema->getTable($table)->dropColumn($col);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        foreach(['form', 'enrollment'] as $table) {
            foreach(['created_by', 'updated_by'] as $col) {
                $schema->getTable($table)->addColumn($col, 'string')->setNotnull(false);
            }
        }
    }
}
