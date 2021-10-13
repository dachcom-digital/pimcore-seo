<?php

namespace SeoBundle\Twig\Extension;

use SeoBundle\MetaData\MetaDataProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    protected MetaDataProviderInterface $metaDataProvider;

    public function __construct(MetaDataProviderInterface $metaDataProvider)
    {
        $this->metaDataProvider = $metaDataProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_update_metadata', [$this, 'updateMetadata']),
        ];
    }

    public function updateMetadata(mixed $element, ?string $locale): void
    {
        $this->metaDataProvider->updateSeoElement($element, $locale);
    }

    public function getName(): string
    {
        return 'seo_metadata';
    }
}
