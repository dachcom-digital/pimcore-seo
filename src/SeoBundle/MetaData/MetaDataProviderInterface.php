<?php

namespace SeoBundle\MetaData;

interface MetaDataProviderInterface
{
    /**
     * @param object $element
     */
    public function updateSeoElement($element);
}
