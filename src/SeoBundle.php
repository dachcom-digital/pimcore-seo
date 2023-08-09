<?php

namespace SeoBundle;

use SeoBundle\DependencyInjection\Compiler\ThirdParty\RemoveNewsMetaDataListenerPass;
use SeoBundle\Tool\Install;
use SeoBundle\DependencyInjection\Compiler\MetaDataExtractorPass;
use SeoBundle\DependencyInjection\Compiler\MetaDataIntegratorPass;
use SeoBundle\DependencyInjection\Compiler\MetaMiddlewareAdapterPass;
use SeoBundle\DependencyInjection\Compiler\ThirdParty\RemovePimcoreListenerPass;
use SeoBundle\DependencyInjection\Compiler\ThirdParty\RemoveCoreShopExtractorListenerPass;
use SeoBundle\DependencyInjection\Compiler\ResourceProcessorPass;
use SeoBundle\DependencyInjection\Compiler\IndexWorkerPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

class SeoBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/seo';

    public function getInstaller(): Install
    {
        return $this->container->get(Install::class);
    }

    public function build(ContainerBuilder $container): void
    {
        $this->configureDoctrineExtension($container);

        $container->addCompilerPass(new IndexWorkerPass());
        $container->addCompilerPass(new ResourceProcessorPass());
        $container->addCompilerPass(new MetaDataExtractorPass());
        $container->addCompilerPass(new MetaDataIntegratorPass());
        $container->addCompilerPass(new MetaMiddlewareAdapterPass());

        // third party handling
        $container->addCompilerPass(new RemovePimcoreListenerPass(), PassConfig::TYPE_BEFORE_REMOVING, 250);
        $container->addCompilerPass(new RemoveNewsMetaDataListenerPass(), PassConfig::TYPE_BEFORE_REMOVING, 250);
        $container->addCompilerPass(new RemoveCoreShopExtractorListenerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 250);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    protected function configureDoctrineExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                [$this->getNameSpacePath() => $this->getNamespaceName()],
                ['seo.persistence.doctrine.manager'],
                'seo.persistence.doctrine.enabled'
            )
        );
    }

    protected function getNamespaceName(): string
    {
        return 'SeoBundle\Model';
    }

    protected function getNameSpacePath(): string
    {
        return sprintf(
            '%s/config/doctrine/%s',
            $this->getPath(),
            'model'
        );
    }
}
