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

namespace SeoBundle\EventListener\Admin;

use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Event\BundleManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BundleManagerEvents::CSS_PATHS => 'addCssFiles',
            BundleManagerEvents::JS_PATHS  => 'addJsFiles',
        ];
    }

    public function addCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/seo/css/admin.css'
        ]);
    }

    public function addJsFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/seo/js/plugin.js',
            '/bundles/seo/js/metaData/extension/localizedFieldExtension.js',
            '/bundles/seo/js/metaData/extension/integratorValueFetcher.js',
            '/bundles/seo/js/metaData/extension/hrefFieldExtension.js',
            '/bundles/seo/js/metaData/components/seoHrefTextField.js',
            '/bundles/seo/js/metaData/abstractMetaDataPanel.js',
            '/bundles/seo/js/metaData/documentMetaDataPanel.js',
            '/bundles/seo/js/metaData/objectMetaDataPanel.js',
            '/bundles/seo/js/metaData/integrator/abstractIntegrator.js',
            '/bundles/seo/js/metaData/integrator/titleDescriptionIntegrator.js',
            '/bundles/seo/js/metaData/integrator/htmlTagIntegrator.js',
            '/bundles/seo/js/metaData/integrator/schemaIntegrator.js',
            '/bundles/seo/js/metaData/integrator/abstractPropertyIntegrator.js',
            '/bundles/seo/js/metaData/integrator/propertyIntegrator/item.js',
            '/bundles/seo/js/metaData/integrator/twitterCardIntegrator.js',
            '/bundles/seo/js/metaData/integrator/ogIntegrator.js',
        ]);
    }
}
