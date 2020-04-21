<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenGraphIntegrator implements IntegratorInterface
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
            'livePreviewTemplates' => [
                ['facebook', 'Facebook']
            ],
            'presets'              => $this->configuration['presets'],
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
        $template = in_array($template, ['facebook']) ? $template : 'default';

        if (null === $url = $this->urlGenerator->generate($element)) {
            $url = 'http://localhost/no-url-found';
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
            'path'   => sprintf('@SeoBundle/Resources/views/preview/ogGraph/%s.html.twig', $template),
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
        foreach ($configuration as &$ogField) {
            if ($ogField['property'] === 'og:image' && isset($ogField['value']['thumbPath'])) {
                unset($ogField['value']['thumbPath']);
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

        foreach ($configuration as &$ogField) {
            if ($ogField['property'] === 'og:image') {
                if (null !== $imagePath = $this->getImagePath($ogField['value'])) {
                    $ogField['value']['thumbPath'] = $imagePath;
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

        $addedItems = 0;
        foreach ($data as $ogItem) {

            if (!isset($ogItem['value']) || empty($ogItem['value']) || empty($ogItem['property'])) {
                continue;
            }

            if (null !== $value = $this->findLocaleAwareData($ogItem['property'], $ogItem['value'], $locale)) {
                $addedItems++;
                $seoMetadata->addExtraProperty($ogItem['property'], $value);
            }
        }

        if ($addedItems > 0 && null !== $elementUrl = $this->urlGenerator->generate($element)) {
            $seoMetadata->addExtraProperty('og:url', $elementUrl);
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
        if ($property === 'og:image') {
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

        $defaultPresets = $this->getDefaultPresets();

        $configuration['presets'] = array_merge($defaultPresets, $configuration['presets']);
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
            $thumbnail = $asset->getThumbnail($this->configuration['facebook_image_thumbnail']);
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
                'name' => 'Article',
                'tag'  => 'article'
            ],
            [
                'name' => 'Website',
                'tag'  => 'website'
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultProperties()
    {
        return [
            'og:type',
            'og:title',
            'og:description',
            'og:image',
            'og:image.alt',
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultPresets()
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