<?php

namespace SeoBundle\Registry;

use SeoBundle\ResourceProcessor\ResourceProcessorInterface;

interface ResourceProcessorRegistryInterface
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
     * @return ResourceProcessorInterface
     * @throws \Exception
     */
    public function get($identifier);

    /**
     * @return ResourceProcessorInterface[]
     */
    public function getAll();

}
