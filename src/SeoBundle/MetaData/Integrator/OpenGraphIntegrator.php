<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenGraphIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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

    public function validateBeforePersist(string $elementType, int $elementId, array $data, $previousData = null): ?array
    {
        if ($elementType === 'object') {
            $arrayModifier = new ArrayHelper();
            $data = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'property');
        }

        if (is_array($data) && count($data) === 0) {
            return null;
        }

        return $data;
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

        $defaultProperties = array_map(static function ($value) {
            return [$value, $value];
        }, $this->getDefaultProperties());

        $defaultPresets = $this->getDefaultPresets();

        $configuration['presets'] = array_merge($defaultPresets, $configuration['presets']);
        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $configuration['properties']);

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
            'og:type',
            'og:title',
            'og:description',
            'og:image',
            'og:image.alt',
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
