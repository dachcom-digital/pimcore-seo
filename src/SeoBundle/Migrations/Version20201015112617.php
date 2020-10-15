<?php

namespace SeoBundle\Migrations;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\User\Permission;
use SeoBundle\Tool\Install;

class Version20201015112617 extends AbstractPimcoreMigration
{
    /**
     * @inheritdoc
     */
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     *
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        foreach (Install::REQUIRED_PERMISSION as $permission) {
            $definition = Permission\Definition::getByKey($permission);

            if ($definition instanceof Permission\Definition) {
                continue;
            }

            try {
                Permission\Definition::create($permission);
            } catch (\Throwable $e) {
                throw new AbortMigrationException(sprintf('Failed to create permission "%s": %s', $permission, $e->getMessage()));
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
