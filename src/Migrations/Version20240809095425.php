<?php

declare(strict_types=1);

namespace SeoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240809095425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->getTable('seo_element_meta_data')->hasColumn('release_type')) {
            return;
        }

        $this->addSql('ALTER TABLE seo_element_meta_data ADD release_type VARCHAR(255) DEFAULT "public" NOT NULL;');
        $this->addSql('DROP INDEX element_type_id_integrator ON seo_element_meta_data;');
        $this->addSql('CREATE UNIQUE INDEX element_type_id_integrator ON seo_element_meta_data (element_type, element_id, integrator, release_type);');
    }

    public function down(Schema $schema): void
    {
    }
}