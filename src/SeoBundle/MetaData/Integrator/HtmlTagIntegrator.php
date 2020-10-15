<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlTagIntegrator implements IntegratorInterface
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
        return [
            'hasLivePreview'       => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields'   => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviewParameter($element, ?string $template, array $data)
    {
        return [];
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

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata)
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
