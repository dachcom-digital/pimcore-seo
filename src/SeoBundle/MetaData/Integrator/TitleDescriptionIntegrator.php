<?php

namespace SeoBundle\MetaData\Integrator;

use Pimcore\Model\Document\Page;
use SeoBundle\Model\SeoMetaDataInterface;

class TitleDescriptionIntegrator implements IntegratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBackendConfiguration($element)
    {
        $url = 'http://localhost';

        try {
            $url = $element instanceof Page ? $element->getUrl() : 'http://localhost';
        } catch (\Exception $e) {
            // fail silently
        }

        return [
            'url' => $url
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, array $data, SeoMetaDataInterface $seoMetadata)
    {
        if (!empty($data['description'])) {
            $seoMetadata->setMetaDescription($data['description']);
        }

        if (!empty($data['title'])) {
            $seoMetadata->setTitle($data['title']);
        }
    }
}