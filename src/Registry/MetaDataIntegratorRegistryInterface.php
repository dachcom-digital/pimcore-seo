<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Integrator\IntegratorInterface;

interface MetaDataIntegratorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     */
    public function get(string $identifier): IntegratorInterface;

    /**
     * @return array<int, IntegratorInterface>
     */
    public function getAll(): array;
}
