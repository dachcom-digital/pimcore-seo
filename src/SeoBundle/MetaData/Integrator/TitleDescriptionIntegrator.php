<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleDescriptionIntegrator implements IntegratorInterface
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * {@inheritdoc}
     */
    public function getBackendConfiguration($element)
    {
        $useLocalizedFields = $element instanceof DataObject;

        return [
            'hasLivePreview'       => true,
            'livePreviewTemplates' => [],
            'useLocalizedFields'   => $useLocalizedFields
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviewParameter($element, ?string $template, array $data)
    {
        $url = 'http://localhost';

        try {
            $url = $element instanceof Page ? $element->getUrl() : 'http://localhost';
        } catch (\Exception $e) {
            // fail silently
        }

        $author = 'John Doe';
        $title = isset($data['title']) ? $data['title'] : 'This is a title';
        $description = isset($data['description']) ? $data['description'] : 'This is a very long description which should be not too long.';

        return [
            'path'   => '@SeoBundle/Resources/views/preview/titleDescription/preview.html.twig',
            'params' => [
                'url'         => $url,
                'author'      => $author,
                'title'       => $title,
                'description' => $description,
                'date'        => date('d.m.Y')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforeBackend(string $elementType, int $elementId, array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforePersist(string $elementType, int $elementId, array $data, $previousData = null)
    {
        if ($elementType === 'object') {
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData);
        }

        if (empty($data['title']) && empty($data['description'])) {
            return null;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $previousData
     *
     * @return array
     */
    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData)
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

            $rebuildRow = isset($previousData[$type]) ? $previousData[$type] : [];

            if (!isset($data[$type]) || !is_array($data[$type])) {
                $newData[$type] = $rebuildRow;
                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow);
        }

        return $newData;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata)
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

    /**
     * @param array|string $value
     * @param string       $locale
     *
     * @return mixed|null
     */
    protected function findLocaleAwareData($value, $locale)
    {
        if (!is_array($value)) {
            return $value;
        }

        if (empty($locale)) {
            return null;
        }

        if (count($value) === 0) {
            return null;
        }

        $index = array_search($locale, array_column($value, 'locale'));
        if ($index === false) {
            return null;
        }

        $value = $value[$index]['value'];

        if (empty($value) || !is_scalar($value)) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(OptionsResolver $resolver)
    {
        // no options here.
    }
}
