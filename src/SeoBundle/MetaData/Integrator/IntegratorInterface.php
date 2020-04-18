<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface IntegratorInterface
{
    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration);

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver);

    /**
     * @param mixed $element
     *
     * @return array
     */
    public function getBackendConfiguration($element);

    /**
     * @param string $elementType
     * @param int    $elementId
     * @param array  $data
     *
     * @return array
     */
    public function validateBeforeBackend(string $elementType, int $elementId, array $data);

    /**
     * @param string $elementType
     * @param int    $elementId
     * @param array  $data
     *
     * @return array|null
     */
    public function validateBeforePersist(string $elementType, int $elementId, array $data);

    /**
     * @param mixed       $element
     * @param string|null $template
     * @param array       $data
     *
     * @return array
     */
    public function getPreviewParameter($element, ?string $template, array $data);

    /**
     * @param mixed                $element
     * @param array                $data
     * @param string|null          $locale
     * @param SeoMetaDataInterface $seoMetadata
     */
    public function updateMetaData($element, array $data,  ?string $locale, SeoMetaDataInterface $seoMetadata);
}