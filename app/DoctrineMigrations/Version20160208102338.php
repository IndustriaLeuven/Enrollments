<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160208102338 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $form = $schema->getTable('form');
        $form->addColumn('auth_edit_form', 'simple_array')->setNotnull(false);
        $form->addColumn('auth_list_enrollments', 'simple_array')->setNotnull(false);
        $form->addColumn('auth_edit_enrollments', 'simple_array')->setNotnull(false);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->getTable('form')
            ->dropColumn('auth_edit_form')
            ->dropColumn('auth_list_enrollments')
            ->dropColumn('auth_edit_enrollments')
        ;
    }
}
