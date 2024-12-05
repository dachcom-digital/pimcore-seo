<?php

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

namespace SeoBundle\Tool;

use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\User\Permission;
use Pimcore\Security\User\TokenStorageUserResolver;
use SeoBundle\Migrations\Version20240827080929;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class Install extends SettingsStoreAwareInstaller
{
    public const REQUIRED_PERMISSION = [
        'seo_bundle_remove_property',
        'seo_bundle_add_property',
    ];

    protected TokenStorageUserResolver $resolver;
    protected DecoderInterface $serializer;

    public function setTokenStorageUserResolver(TokenStorageUserResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function setSerializer(DecoderInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function install(): void
    {
        $this->installDbStructure();
        $this->installPermissions();

        parent::install();
    }

    public function getLastMigrationVersionClassName(): ?string
    {
        return Version20240827080929::class;
    }

    protected function installDbStructure(): void
    {
        $db = \Pimcore\Db::get();
        $db->executeQuery(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
    }

    protected function installPermissions(): void
    {
        foreach (self::REQUIRED_PERMISSION as $permission) {
            $definition = Permission\Definition::getByKey($permission);

            if ($definition) {
                continue;
            }

            try {
                Permission\Definition::create($permission);
            } catch (\Throwable $e) {
                throw new InstallationException(sprintf('Failed to create permission "%s": %s', $permission, $e->getMessage()));
            }
        }
    }

    protected function getInstallSourcesPath(): string
    {
        return __DIR__ . '/../../config/install';
    }
}
