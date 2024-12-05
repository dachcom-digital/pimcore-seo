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

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleDescriptionIntegrator extends AbstractIntegrator implements IntegratorInterface, XliffAwareIntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration($element): array
    {
        return [
            'hasLivePreview'       => true,
            'livePreviewTemplates' => [],
            'useLocalizedFields'   => $element instanceof DataObject
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        $url = 'http://localhost';

        try {
            $url = $element instanceof Page ? $element->getUrl() : 'http://localhost';
        } catch (\Exception $e) {
            // fail silently
        }

        $author = 'John Doe';
        $title = $data['title'] ?? 'This is a title';
        $description = $data['description'] ?? 'This is a very long description which should be not too long.';

        return [
            'path'   => '@Seo/preview/titleDescription/preview.html.twig',
            'params' => [
                'url'         => $url,
                'author'      => $author,
                'title'       => $title,
                'description' => $description,
                'date'        => date('d.m.Y')
            ]
        ];
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function validateBeforeXliffExport(string $elementType, int $elementId, array $data, string $locale): array
    {
        $transformedData = $this->validateBeforeBackend($elementType, $elementId, $data);

        $exportData = [];
        foreach ($transformedData as $fieldName => $fieldData) {
            $exportData[$fieldName] = $this->findData($fieldData, $locale);
        }

        return $exportData;
    }

    public function validateBeforeXliffImport(string $elementType, int $elementId, array $data, string $locale): ?array
    {
        $parsedData = [];

        foreach ($data as $property => $value) {
            $parsedData[$property] = $elementType === 'object' ? [
                [
                    'locale' => $locale,
                    'value'  => $value
                ]
            ] : $value;
        }

        return $parsedData;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if ($elementType === 'object') {
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData, $merge);
        }

        if (empty($data['title']) && empty($data['description'])) {
            return null;
        }

        return $data;
    }

    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData, bool $mergeWithPrevious = false): array
    {
        $arrayModifier = new ArrayHelper();

        // nothing to merge, just clean up
        if (!is_array($previousData) || count($previousData) === 0) {
            return [
                'title'       => $arrayModifier->cleanEmptyLocaleRows($data['title']),
                'description' => $arrayModifier->cleanEmptyLocaleRows($data['description'])
            ];
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        foreach (['title', 'description'] as $type) {
            $rebuildRow = $previousData[$type] ?? [];

            if (!isset($data[$type]) || !is_array($data[$type])) {
                $newData[$type] = $rebuildRow;

                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow, $mergeWithPrevious);
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (null !== $value = $this->findLocaleAwareData($data['description'] ?? null, $locale)) {
            $seoMetadata->setMetaDescription($value);
        }

        if (null !== $value = $this->findLocaleAwareData($data['title'] ?? null, $locale)) {
            $seoMetadata->setTitle($value);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        // no options here.
    }
}
