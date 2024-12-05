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

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenGraphIntegrator extends AbstractIntegrator implements IntegratorInterface, XliffAwareIntegratorInterface
{
    protected array $configuration;

    public function __construct(protected UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getBackendConfiguration($element): array
    {
        $useLocalizedFields = $element instanceof DataObject;

        return [
            'hasLivePreview'       => true,
            'livePreviewTemplates' => [
                ['facebook', 'Facebook']
            ],
            'presets'              => $this->configuration['presets'],
            'properties'           => $this->configuration['properties'],
            'types'                => $this->configuration['types'],
            'useLocalizedFields'   => $useLocalizedFields,
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        $template = $template === 'facebook' ? $template : 'default';

        if (null === $url = $this->urlGenerator->generate($element)) {
            $url = 'http://localhost/no-url-found';
        }

        $title = $data['title'] ?? 'This is a title';
        $description = $data['description'] ?? 'This is a very long description which should be not too long.';

        $imagePath = 'bundles/seo/img/integrator/demoImage.jpg';
        if (isset($data['image']) && is_array($data['image'])) {
            if (null !== $thumbImagePath = $this->getImagePath($data['image'])) {
                $imagePath = $thumbImagePath;
            }
        }

        return [
            'path'   => sprintf('@Seo/preview/ogGraph/%s.html.twig', $template),
            'params' => [
                'title'       => $title,
                'description' => $description,
                'imagePath'   => $imagePath,
                'url'         => $url
            ]
        ];
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        foreach ($data as &$ogField) {
            if ($ogField['property'] === 'og:image' && isset($ogField['value']['thumbPath'])) {
                unset($ogField['value']['thumbPath']);
            }
        }

        return $data;
    }

    public function validateBeforeXliffExport(string $elementType, int $elementId, array $data, string $locale): array
    {
        $transformedData = $this->validateBeforeBackend($elementType, $elementId, $data);

        $exportData = [];

        foreach ($transformedData as $fieldData) {
            $fieldName = $fieldData['property'];
            $propertyIndex = array_search($fieldName, array_column($this->configuration['properties'], 0), true);

            if ($propertyIndex === false) {
                continue;
            }

            $propertyDefinition = $this->configuration['properties'][$propertyIndex];
            if ($propertyDefinition[2] === false) {
                continue;
            }

            $exportData[$fieldName] = $this->findData($fieldData['value'], $locale);
        }

        return $exportData;
    }

    public function validateBeforeXliffImport(string $elementType, int $elementId, array $data, string $locale): ?array
    {
        $parsedData = [];

        foreach ($data as $property => $value) {
            $parsedData[] = [
                'property' => $property,
                'value'    => $elementType === 'object' ? [
                    [
                        'locale' => $locale,
                        'value'  => $value
                    ]
                ] : $value
            ];
        }

        return $parsedData;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        $arrayModifier = new ArrayHelper();

        if ($elementType === 'object') {
            $newData = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'property', 'value', $merge);
        } else {
            $newData = $arrayModifier->mergeNonLocaleAwareArrays($data, $previousData, 'property', $merge);
        }

        if (is_array($newData) && count($newData) === 0) {
            return null;
        }

        return $newData;
    }

    public function updateMetaData($element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (count($data) === 0) {
            return;
        }

        $addedItems = 0;
        foreach ($data as $ogItem) {
            if (empty($ogItem['value']) || empty($ogItem['property'])) {
                continue;
            }

            $propertyName = $ogItem['property'];
            $propertyValue = $ogItem['value'];

            if ($propertyName === 'og:image') {
                $value = isset($propertyValue['id']) && is_numeric($propertyValue['id']) ? $this->getImagePath($propertyValue) : null;
            } else {
                $value = $this->findLocaleAwareData($propertyValue, $locale);
            }

            if ($value === null) {
                continue;
            }

            $addedItems++;
            $seoMetadata->addExtraProperty($propertyName, $value);
        }

        if ($addedItems > 0 && null !== $elementUrl = $this->urlGenerator->generate($element)) {
            $seoMetadata->addExtraProperty('og:url', $elementUrl);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $defaultTypes = array_map(static function ($value) {
            return [$value['name'], $value['tag']];
        }, $this->getDefaultTypes());

        $defaultPresets = $this->getDefaultPresets();
        $defaultProperties = $this->getDefaultProperties();

        $defaultProperties = array_map(static function ($translatable, $key) {
            return [$key, $key, $translatable];
        }, $defaultProperties, array_keys($defaultProperties));

        $additionalProperties = array_map(static function (array $row) {
            return count($row) === 2 ? [$row[0], $row[1], false] : $row;
        }, $configuration['properties']);

        $configuration['presets'] = array_merge($defaultPresets, $configuration['presets']);
        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $additionalProperties);

        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'facebook_image_thumbnail' => null,
            'presets'                  => [],
            'types'                    => [],
            'properties'               => []
        ]);

        $resolver->setRequired(['facebook_image_thumbnail']);
        $resolver->setAllowedTypes('facebook_image_thumbnail', ['string']);
        $resolver->setAllowedTypes('presets', ['array']);
        $resolver->setAllowedTypes('types', ['array']);
        $resolver->setAllowedTypes('properties', ['array']);
    }

    protected function getImagePath(array $data): ?string
    {
        if (!array_key_exists('id', $data)) {
            return null;
        }

        $asset = Asset::getById($data['id']);

        if (!$asset instanceof Asset) {
            return null;
        }

        return $this->urlGenerator->generate($asset, ['thumbnail' => $this->configuration['facebook_image_thumbnail']]);
    }

    protected function getDefaultTypes(): array
    {
        return [
            [
                'name' => 'Article',
                'tag'  => 'article'
            ],
            [
                'name' => 'Website',
                'tag'  => 'website'
            ],
        ];
    }

    protected function getDefaultProperties(): array
    {
        return [
            'og:type'        => false,
            'og:title'       => true,
            'og:description' => true,
            'og:image'       => false,
            'og:image.alt'   => true,
        ];
    }

    protected function getDefaultPresets(): array
    {
        return [
            [
                'label'      => 'Facebook',
                'icon_class' => 'pimcore_icon_user',
                'fields'     => [
                    [
                        'property' => 'og:type',
                        'content'  => 'article',
                    ],
                    [
                        'property' => 'og:description',
                        'content'  => null,
                    ],
                    [
                        'property' => 'og:title',
                        'content'  => null,
                    ]
                ]
            ]
        ];
    }
}
