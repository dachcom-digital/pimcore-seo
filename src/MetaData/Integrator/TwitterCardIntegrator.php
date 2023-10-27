<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterCardIntegrator extends AbstractIntegrator implements IntegratorInterface, XliffAwareIntegratorInterface
{
    protected array $configuration;

    public function __construct(protected UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getBackendConfiguration(mixed $element): array
    {
        return [
            'hasLivePreview'       => true,
            'livePreviewTemplates' => [],
            'properties'           => $this->configuration['properties'],
            'types'                => $this->configuration['types'],
            'useLocalizedFields'   => $element instanceof DataObject,
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        $url = $this->urlGenerator->getCurrentSchemeAndHost();

        $title = $data['title'] ?? 'This is a title';
        $description = $data['description'] ?? 'This is a very long description which should be not too long.';

        $imagePath = 'bundles/seo/img/integrator/demoImage.jpg';
        if (isset($data['image']) && is_array($data['image'])) {
            if (null !== $thumbImagePath = $this->getImagePath($data['image'])) {
                $imagePath = $thumbImagePath;
            }
        }

        return [
            'path'   => '@Seo/preview/twitterCard/preview.html.twig',
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
        foreach ($data as &$twitterItem) {
            if ($twitterItem['name'] === 'twitter:image' && isset($twitterItem['value']['thumbPath'])) {
                unset($twitterItem['value']['thumbPath']);
            }
        }

        return $data;
    }

    public function validateBeforeXliffExport(string $elementType, int $elementId, array $data, string $locale): array
    {
        $transformedData = $this->validateBeforeBackend($elementType, $elementId, $data);

        $exportData = [];

        foreach ($transformedData as $fieldData) {

            $fieldName = $fieldData['name'];
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
                'name'  => $property,
                'value' => $elementType === 'object' ? [
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
            $newData = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'name', 'value', $merge);
        } else {
            $newData = $arrayModifier->mergeNonLocaleAwareArrays($data, $previousData, 'name', $merge);
        }

        if (is_array($newData) && count($newData) === 0) {
            return null;
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (count($data) === 0) {
            return;
        }

        foreach ($data as $twitterItem) {

            if (empty($twitterItem['value']) || empty($twitterItem['name'])) {
                continue;
            }

            $propertyName = $twitterItem['name'];
            $propertyValue = $twitterItem['value'];

            if ($propertyName === 'twitter:image') {
                $value = isset($propertyValue['id']) && is_numeric($propertyValue['id']) ? $this->getImagePath($propertyValue) : null;
            } else {
                $value = $this->findLocaleAwareData($propertyValue, $locale);
            }

            if ($value === null) {
                continue;
            }

            $seoMetadata->addExtraName($propertyName, $value);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $defaultTypes = array_map(static function ($value) {
            return [$value['name'], $value['tag']];
        }, $this->getDefaultTypes());

        $defaultProperties = $this->getDefaultProperties();
        $defaultProperties = array_map(static function ($translatable, $key) {
            return [$key, $key, $translatable];
        }, $defaultProperties, array_keys($defaultProperties));

        $additionalProperties = array_map(static function (array $row) {
            return count($row) === 2 ? [$row[0], $row[1], false] : $row;
        }, $configuration['properties']);

        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $additionalProperties);

        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'twitter_image_thumbnail' => null,
            'types'                   => [],
            'properties'              => []
        ]);

        $resolver->setRequired(['twitter_image_thumbnail']);
        $resolver->setAllowedTypes('twitter_image_thumbnail', ['string']);
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

        return $this->urlGenerator->generate($asset, ['thumbnail' => $this->configuration['twitter_image_thumbnail']]);
    }

    protected function getDefaultTypes(): array
    {
        return [
            [
                'name' => 'Summary',
                'tag'  => 'summary'
            ],
            [
                'name' => 'Summary (Large Image)',
                'tag'  => 'summary_large_image'
            ],
            [
                'name' => 'App',
                'tag'  => 'app'
            ],
            [
                'name' => 'Player',
                'tag'  => 'player'
            ],
        ];
    }

    protected function getDefaultProperties(): array
    {
        return [
            'twitter:card'                => false,
            'twitter:title'               => true,
            'twitter:description'         => true,
            'twitter:image'               => false,
            'twitter:image:alt'           => true,
            'twitter:site'                => true,
            'twitter:site:id'             => true,
            'twitter:creator'             => true,
            'twitter:creator:id'          => true,
            'twitter:player'              => false,
            'twitter:player:width'        => false,
            'twitter:player:height'       => false,
            'twitter:player:stream'       => true,
            'twitter:app:name:iphone'     => true,
            'twitter:app:id:iphone'       => true,
            'twitter:app:url:iphone'      => true,
            'twitter:app:name:ipad'       => true,
            'twitter:app:id:ipad'         => true,
            'twitter:app:url:ipad'        => true,
            'twitter:app:name:googleplay' => true,
            'twitter:app:id:googleplay'   => true,
            'twitter:app:url:googleplay'  => true
        ];
    }
}
