<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160321194211 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach(['form', 'enrollment'] as $table) {
            foreach(['created_by', 'updated_by'] as $col) {
                $this->addSql('UPDATE '.$table.' SET '.$col.'_id = (SELECT id FROM app_user WHERE auth_id = '.$col.')');
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
                $this->addSql('UPDATE '.$table.' SET '.$col.' = (SELECT auth_id FROM app_user WHERE id = '.$col.'_id)');
            }
        }

    }
}
