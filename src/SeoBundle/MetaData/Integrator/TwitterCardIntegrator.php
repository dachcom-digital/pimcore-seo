<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterCardIntegrator implements IntegratorInterface
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendConfiguration($element)
    {
        $useLocalizedFields = $element instanceof DataObject;

        return [
            'hasLivePreview'       => true,
            'livePreviewTemplates' => [],
            'properties'           => $this->configuration['properties'],
            'types'                => $this->configuration['types'],
            'useLocalizedFields'   => $useLocalizedFields,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviewParameter($element, ?string $template, array $data)
    {
        if (null === $url = $this->urlGenerator->getCurrentSchemeAndHost()) {
            $url = 'http://localhost';
        }

        $title = isset($data['title']) ? $data['title'] : 'This is a title';
        $description = isset($data['description']) ? $data['description'] : 'This is a very long description which should be not too long.';

        $imagePath = 'bundles/seo/img/integrator/demoImage.jpg';
        if (isset($data['image']) && is_array($data['image'])) {
            if (null !== $thumbImagePath = $this->getImagePath($data['image'])) {
                $imagePath = $thumbImagePath;
            }
        }

        return [
            'path'   => '@SeoBundle/Resources/views/preview/twitterCard/preview.html.twig',
            'params' => [
                'title'       => $title,
                'description' => $description,
                'imagePath'   => $imagePath,
                'url'         => $url
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforeBackend(string $elementType, int $elementId, array $configuration)
    {
        foreach ($configuration as &$twitterItem) {
            if ($twitterItem['name'] === 'twitter:image' && isset($twitterItem['value']['thumbPath'])) {
                unset($twitterItem['value']['thumbPath']);
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforePersist(string $elementType, int $elementId, array $configuration)
    {
        if (is_array($configuration) && count($configuration) === 0) {
            return null;
        }

        foreach ($configuration as &$twitterItem) {
            if ($twitterItem['name'] === 'twitter:image') {
                if (null !== $imagePath = $this->getImagePath($twitterItem['value'])) {
                    $twitterItem['value']['thumbPath'] = $imagePath;
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata)
    {
        if (count($data) === 0) {
            return;
        }

        foreach ($data as $twitterItem) {
            if (!isset($twitterItem['value']) || empty($twitterItem['value']) || empty($twitterItem['name'])) {
                continue;
            }

            if (null !== $value = $this->findLocaleAwareData($twitterItem['name'], $twitterItem['value'], $locale)) {
                $seoMetadata->addExtraName($twitterItem['name'], $value);
            }
        }
    }

    /**
     * @param string       $property
     * @param array|string $value
     * @param string       $locale
     *
     * @return string|null
     */
    protected function findLocaleAwareData(string $property, $value, $locale)
    {
        if ($property === 'twitter:image') {
            return isset($value['thumbPath']) && !empty($value['thumbPath']) ? $value['thumbPath'] : null;
        }

        if (!is_array($value)) {
            return $value;
        }

        if (empty($locale)) {
            return $value;
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
        $defaultTypes = array_map(function ($value) {
            return [$value['name'], $value['tag']];
        }, $this->getDefaultTypes());

        $defaultProperties = array_map(function ($value) {
            return [$value, $value];
        }, $this->getDefaultProperties());

        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $configuration['properties']);

        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(OptionsResolver $resolver)
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

    /**
     * @param array $data
     *
     * @return string|null
     */
    protected function getImagePath(array $data)
    {
        $imagePath = null;

        $asset = Asset::getById($data['id']);
        if ($asset instanceof Asset\Image) {
            $thumbnail = $asset->getThumbnail($this->configuration['twitter_image_thumbnail']);
            if ($thumbnail instanceof Asset\Image\Thumbnail) {
                $imagePath = $thumbnail->getPath(false);
            }
        }

        return $imagePath;
    }

    /**
     * @return array
     */
    protected function getDefaultTypes()
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

    /**
     * @return array
     */
    protected function getDefaultProperties()
    {
        return [
            'twitter:card',
            'twitter:title',
            'twitter:description',
            'twitter:image',
            'twitter:image:alt',
            'twitter:site',
            'twitter:site:id',
            'twitter:creator',
            'twitter:creator:id',
            'twitter:player',
            'twitter:player:width',
            'twitter:player:height',
            'twitter:player:stream',
            'twitter:app:name:iphone',
            'twitter:app:id:iphone',
            'twitter:app:url:iphone',
            'twitter:app:name:ipad',
            'twitter:app:id:ipad',
            'twitter:app:url:ipad',
            'twitter:app:name:googleplay',
            'twitter:app:id:googleplay',
            'twitter:app:url:googleplay'
        ];
    }
}
