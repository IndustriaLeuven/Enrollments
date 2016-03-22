<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160321191126 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach([$schema->getTable('form'), $schema->getTable('enrollment')] as $table) {
            /* @var $table Table */
            foreach(['created_by_id', 'updated_by_id'] as $col) {
                $table->addColumn($col, 'integer')->setNotNull(false);
                $table->addForeignKeyConstraint($schema->getTable('app_user'), array($col), array('id'));
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        foreach([$schema->getTable('form'), $schema->getTable('enrollment')] as $table) {
            /* @var $table Table */
            foreach(['created_by_id', 'updated_by_id'] as $col) {
                $table->dropColumn($col);
                foreach($table->getForeignKeys() as $fk) {
                    if($fk->getLocalColumns() === [$col])
                        $table->removeForeignKey($fk->getName());
                }
            }
        }

    }
}
