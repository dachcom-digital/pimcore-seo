<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleDescriptionIntegrator implements IntegratorInterface
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

    public function validateBeforePersist(string $elementType, int $elementId, array $data, $previousData = null): ?array
    {
        if ($elementType === 'object') {
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData);
        }

        if (empty($data['title']) && empty($data['description'])) {
            return null;
        }

        return $data;
    }

    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData): array
    {
        $arrayModifier = new ArrayHelper();

        // nothing to merge, just clean up
        if (!is_array($previousData) || count($previousData) === 0) {
            return [
                'title'       => $arrayModifier->cleanEmptyLocaleRows($data['title']),
                'description' => $arrayModifier->cleanEmptyLocaleRows($data['description'])
            ];
        }

        $newData = [];

        foreach (['title', 'description'] as $type) {

            $rebuildRow = $previousData[$type] ?? [];

            if (!isset($data[$type]) || !is_array($data[$type])) {
                $newData[$type] = $rebuildRow;
                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow);
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (!empty($data['description'])) {
            if (null !== $value = $this->findLocaleAwareData($data['description'], $locale)) {
                $seoMetadata->setMetaDescription($value);
            }
        }

        if (!empty($data['title'])) {
            if (null !== $value = $this->findLocaleAwareData($data['title'], $locale)) {
                $seoMetadata->setTitle($value);
            }
        }
    }

    protected function findLocaleAwareData(mixed $value, ?string $locale): int|float|string|bool|null
    {
        if (!is_array($value)) {
            return $value;
        }

        if (count($value) === 0) {
            return null;
        }

        if (empty($locale)) {
            return null;
        }

        $index = array_search($locale, array_column($value, 'locale'), true);
        if ($index === false) {
            return null;
        }

        $value = $value[$index]['value'];

        if (empty($value) || !is_scalar($value)) {
            return null;
        }

        return $value;
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
