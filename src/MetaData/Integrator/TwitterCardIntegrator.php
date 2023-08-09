<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Tool\UrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterCardIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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

    public function validateBeforePersist(string $elementType, int $elementId, array $data, $previousData = null): ?array
    {
        if ($elementType === 'object') {
            $arrayModifier = new ArrayHelper();
            $data = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'name');
        }

        if (is_array($data) && count($data) === 0) {
            return null;
        }

        return $data;
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

        $defaultProperties = array_map(static function ($value) {
            return [$value, $value];
        }, $this->getDefaultProperties());

        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $configuration['properties']);

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
