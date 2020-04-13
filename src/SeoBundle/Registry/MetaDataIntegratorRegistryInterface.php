<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Integrator\IntegratorInterface;

interface MetaDataIntegratorRegistryInterface
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
     * @return IntegratorInterface
     * @throws \Exception
     */
    public function get($identifier);

    /**
     * @return IntegratorInterface[]
     */
    public function getAll();
}
