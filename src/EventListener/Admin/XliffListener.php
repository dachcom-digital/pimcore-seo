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

use Pimcore\Bundle\XliffBundle\Event\Model\TranslationXliffEvent;
use Pimcore\Bundle\XliffBundle\Event\XliffEvents;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\MetaData\MetaDataProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class XliffListener implements EventSubscriberInterface
{
    protected const XLIFF_TYPE = 'dachcom_seo';

    public function __construct(
        protected ElementMetaDataManagerInterface $elementMetaDataManager,
        protected MetaDataProviderInterface $metaDataProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            XliffEvents::XLIFF_ATTRIBUTE_SET_EXPORT => 'export',
            XliffEvents::XLIFF_ATTRIBUTE_SET_IMPORT => 'import',
        ];
    }

    public function export(TranslationXliffEvent $event): void
    {
        $attributeSet = $event->getAttributeSet();
        $element = $attributeSet->getTranslationItem()->getElement();

        $sourceLanguage = $attributeSet->getSourceLanguage();
        $elementType = $this->determinateType($element);

        if ($elementType === null) {
            return;
        }

        $metaData = $this->elementMetaDataManager->getElementDataForXliffExport($elementType, $element->getId(), $sourceLanguage);

        foreach ($metaData as $integrator => $integratorValues) {
            foreach ($integratorValues as $property => $value) {
                $attributeSet->addAttribute(
                    self::XLIFF_TYPE,
                    sprintf('%s#%s', $integrator, $property),
                    $value ?? '',
                    false,
                    []
                );
            }
        }
    }

    public function import(TranslationXliffEvent $event): void
    {
        $attributeSet = $event->getAttributeSet();
        $element = $attributeSet->getTranslationItem()->getElement();

        if ($attributeSet->isEmpty()) {
            return;
        }

        $targetLanguage = $attributeSet->getTargetLanguages()[0];
        $elementType = $this->determinateType($element);

        if ($elementType === null) {
            return;
        }

        $rawData = [];
        foreach ($attributeSet->getAttributes() as $attribute) {
            if ($attribute->getType() !== self::XLIFF_TYPE) {
                continue;
            }

            $attributeName = $attribute->getName();

            [$integrator, $property] = explode('#', $attributeName);

            if (!array_key_exists($integrator, $rawData)) {
                $rawData[$integrator] = [];
            }

            $rawData[$integrator][$property] = $attribute->getContent();
        }

        $this->elementMetaDataManager->saveElementDataFromXliffImport($elementType, $element->getId(), $rawData, $targetLanguage);
    }

    private function determinateType(ElementInterface $element): ?string
    {
        if ($element instanceof Document) {
            return 'document';
        }

        if ($element instanceof DataObject) {
            return 'object';
        }

        return null;
    }
}
