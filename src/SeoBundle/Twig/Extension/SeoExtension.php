<?php

namespace SeoBundle\Twig\Extension;

use SeoBundle\MetaData\MetaDataProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    /**
     * @var MetaDataProviderInterface
     */
    protected $metaDataProvider;

    /**
     * @param MetaDataProviderInterface $metaDataProvider
     */
    public function __construct(MetaDataProviderInterface $metaDataProvider)
    {
        $this->metaDataProvider = $metaDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('seo_update_metadata', [$this, 'updateMetadata']),
        ];
    }

    /**
     * @param object      $element
     * @param string|null $locale
     */
    public function updateMetadata($element, ?string $locale)
    {
        $this->metaDataProvider->updateSeoElement($element, $locale);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo_metadata';
    }
}
