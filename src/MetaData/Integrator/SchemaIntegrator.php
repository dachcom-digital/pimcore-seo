<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\DataObject;
use SeoBundle\Helper\ArrayHelper;
use SeoBundle\MetaData\MetaDataProviderInterface;
use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemaIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;
    protected MetaDataProviderInterface $metaDataProvider;

    public function __construct(MetaDataProviderInterface $metaDataProvider)
    {
        $this->metaDataProvider = $metaDataProvider;
    }

    public function getBackendConfiguration(mixed $element): array
    {
        $useLocalizedFields = $element instanceof DataObject;
        $hasDynamicallyAddedJsonLdData = false;
        $addedJsonLdDataTypes = [];

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
            if (count($schemaBlocks) > 0) {
                $hasDynamicallyAddedJsonLdData = true;
                foreach ($schemaBlocks as $schemaBlock) {
                    if (isset($schemaBlock['@type'])) {
                        if (!isset($addedJsonLdDataTypes[$schemaBlock['@type']])) {
                            $addedJsonLdDataTypes[$schemaBlock['@type']] = 0;
                        }
                        $addedJsonLdDataTypes[$schemaBlock['@type']]++;
                    }
                }
            }
        }

        return [
            'hasDynamicallyAddedJsonLdData'   => $hasDynamicallyAddedJsonLdData,
            'dynamicallyAddedJsonLdDataTypes' => $addedJsonLdDataTypes,
            'useLocalizedFields'              => $useLocalizedFields,
            'hasLivePreview'                  => false,
            'livePreviewTemplates'            => [],
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        return [];
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        if (count($data) === 0) {
            return $data;
        }

        $schemaBlocksConfiguration = [];
        $cleanData = static function (array $schemaBlock) {
            $cleanData = json_encode($schemaBlock, JSON_PRETTY_PRINT);

            return sprintf('<script type="application/ld+json">%s</script>', $cleanData);
        };

        foreach ($data as $schemaBlock) {
            if ($schemaBlock['localized'] === false) {
                $schemaBlocksConfiguration[] = ['localized' => false, 'data' => $cleanData($schemaBlock['data'])];
            } elseif ($schemaBlock['localized'] === true) {
                $localizedSchemaBlocksConfiguration = [];
                foreach ($schemaBlock['data'] as $localizedSchemaBlockValue) {
                    $localizedSchemaBlocksConfiguration[] = [
                        'locale' => $localizedSchemaBlockValue['locale'],
                        'value'  => $cleanData($localizedSchemaBlockValue['value'])
                    ];
                }

                $schemaBlocksConfiguration[] = [
                    'localized'  => true,
                    'data'       => $localizedSchemaBlocksConfiguration,
                    'identifier' => $schemaBlock['identifier']
                ];
            }
        }

        return $schemaBlocksConfiguration;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if (count($data) === 0) {
            return null;
        }

        // assert identifier
        foreach ($data as $idx => $row) {
            if (empty($row['identifier'])) {
                $data[$idx]['identifier'] = uniqid('si', true);
            }
        }

        if ($elementType === 'object') {
            $arrayModifier = new ArrayHelper();
            $data = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'identifier', 'data');
        }

        foreach ($data as $index => $schemaBlock) {
            $schemaBlockData = null;
            $localized = false;

            if ($schemaBlock['localized'] === false) {
                $schemaBlockData = $this->validateSchemaBlock($schemaBlock['data']);
            } elseif ($schemaBlock['localized'] === true) {
                $localized = true;
                $localizedSchemaBlockValues = [];
                foreach ($schemaBlock['data'] as $localizedSchemaBlockValue) {
                    if (null !== $localizedSchemaBlockData = $this->validateSchemaBlock($localizedSchemaBlockValue['value'])) {
                        $localizedSchemaBlockValues[] = [
                            'locale' => $localizedSchemaBlockValue['locale'],
                            'value'  => $localizedSchemaBlockData
                        ];
                    }
                }
                if (count($localizedSchemaBlockValues) > 0) {
                    $schemaBlockData = $localizedSchemaBlockValues;
                }
            }

            if ($schemaBlockData === null) {
                unset($data[$index]);

                continue;
            }

            $data[$index] = [
                'localized'  => $localized,
                'data'       => $schemaBlockData,
                'identifier' => $schemaBlock['identifier']
            ];
        }

        $indexedData = array_values($data);

        if (count($indexedData) === 0) {
            return null;
        }

        return $indexedData;
    }

    public function updateMetaData($element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (count($data) === 0) {
            return;
        }

        foreach ($data as $schemaBlock) {

            if ($schemaBlock['localized'] === false) {
                $value = $schemaBlock['data'];
            } else {
                $value = count($schemaBlock['data']) === 0 ? null : $this->findLocaleAwareData($schemaBlock['data'], $locale, 'array');
            }

            if ($value === null) {
                continue;
            }

            $seoMetadata->addSchema($value);
        }
    }

    protected function validateSchemaBlock(mixed $data): ?array
    {
        // already validated
        if (is_array($data)) {
            return $data;
        }

        if (!is_string($data)) {
            return null;
        }

        try {
            $validatedJsonData = $this->validateJsonLd($data);
        } catch (\Throwable $e) {
            return null;
        }

        if ($validatedJsonData === false) {
            return null;
        }

        return $validatedJsonData;
    }

    protected function validateJsonLd(string $jsonLdData): bool|array
    {
        $jsonLdData = preg_replace(
            '/[ \t\n]+/',
            ' ',
            preg_replace('/\s*$^\s*/m', ' ', $jsonLdData)
        );

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        libxml_use_internal_errors(1);

        $dom->loadHTML(sprintf('%s%s', '<?xml encoding="UTF-8">', $jsonLdData));

        $xpath = new \DOMXPath($dom);
        $jsonScripts = $xpath->query('//script[@type="application/ld+json"]');

        // Handle CDATA stuff
        if (isset($jsonScripts->item(1)->nodeValue)) {
            $json = $jsonScripts->item(1)->nodeValue;
        } else {
            $json = $jsonScripts->item(0)->nodeValue;
        }

        try {
            $data = json_decode(trim($json), true, 512, JSON_THROW_ON_ERROR);
        } catch(\Throwable $e) {
            return false;
        }

        return $data ?? false;
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
