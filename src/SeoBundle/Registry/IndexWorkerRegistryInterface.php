<?php

namespace SeoBundle\Registry;

use SeoBundle\Worker\IndexWorkerInterface;

interface IndexWorkerRegistryInterface
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
     * @return IndexWorkerInterface
     * @throws \Exception
     */
    public function get($identifier);

    /**
     * @return IndexWorkerInterface[]
     */
    public function getAll();
}
