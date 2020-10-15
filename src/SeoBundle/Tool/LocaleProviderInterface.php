<?php

namespace SeoBundle\Tool;

use Pimcore\Model\DataObject;

interface LocaleProviderInterface
{
    /**
     * @param DataObject\AbstractObject|null $object
     *
     * @return array
     */
    public function getAllowedLocalesForObject(?DataObject\AbstractObject $object);
}
