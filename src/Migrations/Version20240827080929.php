<?php

declare(strict_types=1);

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240827080929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->getTable('seo_element_meta_data')->hasIndex('element_type_id_integrator')) {
            return;
        }

        $this->addSql('DROP INDEX element_type_id_integrator ON seo_element_meta_data;');
        $this->addSql('CREATE UNIQUE INDEX element_type_id_integrator ON seo_element_meta_data (element_type, element_id, integrator, release_type);');
    }

    public function down(Schema $schema): void
    {
    }
}
