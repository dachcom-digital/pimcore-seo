<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenGraphIntegrator implements IntegratorInterface
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
            'livePreviewTemplates' => [
                ['facebook', 'Facebook']
            ],
            'presets'              => $this->configuration['presets'],
            'ogProperties'         => $this->configuration['og_properties'],
            'ogTypes'              => $this->configuration['og_types'],
            'useLocalizedFields'   => $useLocalizedFields,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviewParameter($element, ?string $template, array $data)
    {
        $template = in_array($template, ['facebook']) ? $template : 'default';

        $url = 'http://localhost';
        $title = isset($data['title']) ? $data['title'] : 'This is a title';
        $description = isset($data['description']) ? $data['description'] : 'This is a very long description which should be not too long.';

        $imagePath = 'bundles/seo/img/integrator/demoImage.jpg';
        if (isset($data['image']) && is_array($data['image'])) {
            if (null !== $thumbImagePath = $this->getImagePath($data['image'])) {
                $imagePath = $thumbImagePath;
            }
        }

        try {
            $url = $element instanceof Page ? $element->getUrl() : 'http://localhost';
        } catch (\Exception $e) {
            // fail silently
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

        foreach ($data as $ogItem) {

            if (!isset($ogItem['value']) || empty($ogItem['value']) || empty($ogItem['property'])) {
                continue;
            }

            if (null !== $value = $this->findLocaleAwareData($ogItem['property'], $ogItem['value'], $locale)) {
                $seoMetadata->addExtraProperty($ogItem['property'], $value);
            }

        }
    }

    /**
     * @param string       $property
     * @param array|string $value
     * @param string       $locale
     *
     * @return mixed|null
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
        $defaultOgTypes = [
            ['article', 'article'],
            ['restaurant', 'restaurant'],
        ];

        $defaultOgProperties = [
            ['og:type', 'og:type'],
            ['og:title', 'og:title'],
            ['og:description', 'og:description'],
            ['og:image', 'og:image']
        ];

        $defaultPresets = [
            [
                'label'      => 'Facebook',
                'icon_class' => 'pimcore_icon_user',
                'fields'     => [
                    [
                        'property' => 'og:type',
                        'content'  => 'og:article',
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

        $configuration['presets'] = array_merge($defaultPresets, $configuration['presets']);
        $configuration['og_types'] = array_merge($defaultOgTypes, $configuration['og_types']);
        $configuration['og_properties'] = array_merge($defaultOgProperties, $configuration['og_properties']);

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
            'og_types'                 => [],
            'og_properties'            => []
        ]);

        $resolver->setRequired(['facebook_image_thumbnail']);
        $resolver->setAllowedTypes('facebook_image_thumbnail', ['string']);
        $resolver->setAllowedTypes('presets', ['array']);
        $resolver->setAllowedTypes('og_types', ['array']);
        $resolver->setAllowedTypes('og_properties', ['array']);
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
}