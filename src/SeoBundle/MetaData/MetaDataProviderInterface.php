<?php

namespace SeoBundle\MetaData;

interface MetaDataProviderInterface
{
    public function updateSeoElement(mixed $element, ?string $locale): void;
}
