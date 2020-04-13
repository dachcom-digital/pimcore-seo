<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;

interface IntegratorInterface
{
    /**
     * @param mixed $element
     *
     * @return array
     */
    public function getBackendConfiguration($element);

    /**
     * @param mixed                $element
     * @param array                $data
     * @param SeoMetaDataInterface $seoMetadata
     */
    public function updateMetaData($element, array $data, SeoMetaDataInterface $seoMetadata);
}