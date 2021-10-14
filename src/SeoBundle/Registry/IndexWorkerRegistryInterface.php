<?php

namespace SeoBundle\Registry;

use SeoBundle\Worker\IndexWorkerInterface;

interface IndexWorkerRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     */
    public function get(string $identifier): IndexWorkerInterface;

    /**
     * @return array<int, IndexWorkerInterface>
     */
    public function getAll(): array;
}
