<?php

namespace SeoBundle\MetaData;

interface MetaDataProviderInterface
{
    /**
     * @param object      $element
     * @param string|null $locale
     */
    public function updateSeoElement($element, ?string $locale);
}
