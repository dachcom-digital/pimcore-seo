<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlTagIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration(mixed $element): array
    {
        return [
            'hasLivePreview'       => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields'   => false
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        return [];
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, $previousData = null): ?array
    {
        if (is_array($data) && count($data) === 0) {
            return null;
        }

        foreach ($data as $index => $htmlTag) {
            if (!is_string($htmlTag)) {
                unset($data[$index]);

                continue;
            }

            // there must be some html tags in there.
            if ($htmlTag === strip_tags($htmlTag)) {
                unset($data[$index]);

                continue;
            }
        }

        $indexedData = array_values($data);

        if (count($indexedData) === 0) {
            return null;
        }

        return $indexedData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (count($data) === 0) {
            return;
        }

        foreach ($data as $htmlTag) {
            if (is_string($htmlTag)) {
                $seoMetadata->addRaw($htmlTag);
            }
        }
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
