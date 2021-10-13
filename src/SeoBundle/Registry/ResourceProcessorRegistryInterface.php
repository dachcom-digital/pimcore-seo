<?php

namespace SeoBundle\Registry;

use SeoBundle\ResourceProcessor\ResourceProcessorInterface;

interface ResourceProcessorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     */
    public function get(string $identifier): ResourceProcessorInterface;

    /**
     * @return array<int, ResourceProcessorInterface>
     */
    public function getAll(): array;
}
