<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\MetaData\MetaDataProviderInterface;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemaIntegrator implements IntegratorInterface
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var MetaDataProviderInterface
     */
    protected $metaDataProvider;

    public function __construct(MetaDataProviderInterface $metaDataProvider)
    {
        $this->metaDataProvider = $metaDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendConfiguration($element)
    {
        $hasDynamicallyAddedJsonLdData = false;

        foreach (\Pimcore\Tool::getValidLanguages() as $locale) {

            $seoMetaData = null;
            if (method_exists($this->metaDataProvider, 'getSeoMetaDataForBackend')) {
                /** @var SeoMetaDataInterface $seoMetaData */
                $seoMetaData = $this->metaDataProvider->getSeoMetaDataForBackend($element, $locale, ['integrator']);
            }

            if (!$seoMetaData instanceof SeoMetaDataInterface) {
                continue;
            }

            $schemaBlocks = $seoMetaData->getSchema();
            if (is_array($schemaBlocks) && count($schemaBlocks) > 0) {
                $hasDynamicallyAddedJsonLdData = true;
                break;
            }
        }

        return [
            'hasDynamicallyAddedJsonLdData' => $hasDynamicallyAddedJsonLdData,
            'hasLivePreview'                => false,
            'livePreviewTemplates'          => [],
            'useLocalizedFields'            => false
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
        if (!is_array($configuration) || count($configuration) === 0) {
            return $configuration;
        }

        $schemaBlocksConfiguration = [];
        foreach ($configuration as $schemaBlock) {
            $rawData = json_decode($schemaBlock, true);
            $cleanData = json_encode($rawData, JSON_PRETTY_PRINT);
            $schemaBlocksConfiguration[] = sprintf('<script type="application/ld+json">%s</script>', $cleanData);
        }

        return $schemaBlocksConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBeforePersist(string $elementType, int $elementId, array $configuration)
    {
        if (is_array($configuration) && count($configuration) === 0) {
            return null;
        }

        foreach ($configuration as $index => $schemaBlock) {

            if (!is_string($schemaBlock)) {
                unset($configuration[$index]);
                continue;
            }

            try {
                $validatedJsonData = $this->validateJsonLd($schemaBlock);
            } catch (\Throwable $e) {
                unset($configuration[$index]);
                continue;
            }

            if ($validatedJsonData === false) {
                unset($configuration[$index]);
                continue;
            }

            $configuration[$index] = $validatedJsonData;
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

        foreach ($data as $schemaBlock) {
            if (is_string($schemaBlock)) {
                $seoMetadata->addSchema($schemaBlock);
            }
        }
    }

    /**
     * @param string $jsonLdData
     *
     * @return bool
     * @throws \Exception
     */
    protected function validateJsonLd(string $jsonLdData)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(1);
        $dom->loadHTML($jsonLdData);
        $xpath = new \DOMXpath($dom);
        $jsonScripts = $xpath->query('//script[@type="application/ld+json"]');
        $json = trim($jsonScripts->item(0)->nodeValue);

        $data = json_decode($json);

        if ($data === null) {
            return false;
        }

        return json_encode($data);
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