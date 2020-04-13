<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Extractor\ExtractorInterface;

interface MetaDataExtractorRegistryInterface
{
    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier);

    /**
     * @param string $identifier
     *
     * @return ExtractorInterface
     * @throws \Exception
     */
    public function get($identifier);

    /**
     * @return ExtractorInterface[]
     */
    public function getAll();
}
