<?php

declare(strict_types=1);

namespace SeoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230809105540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seo_queue_entry CHANGE uuid uuid BINARY(16) NOT NULL COMMENT "(DC2Type:uuid)";');
    }

    public function down(Schema $schema): void
    {
        // no down
    }
}
