<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
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
    public function validateBeforeBackend(string $elementType, int $elementId, array $configuration)
    {
        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforePersist(string $elementType, int $elementId, array $configuration)
    {
        if (empty($configuration['title']) && empty($configuration['description'])) {
            return null;
        }

        return $configuration;
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