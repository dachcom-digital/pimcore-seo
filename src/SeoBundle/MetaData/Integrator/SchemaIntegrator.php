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
    public function validateBeforeBackend(string $elementType, int $elementId, array $configuration)
    {
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

        foreach ($configuration as $index => $htmlTag) {

            if (!is_string($htmlTag)) {
                unset($configuration[$index]);
                continue;
            }

            // there must be some html tags in there.
            if ($htmlTag === strip_tags($htmlTag)) {
                unset($configuration[$index]);
                continue;
            }
        }

        $indexedConfiguration = array_values($configuration);

        if (count($indexedConfiguration) === 0) {
            return null;
        }

        return $indexedConfiguration;
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