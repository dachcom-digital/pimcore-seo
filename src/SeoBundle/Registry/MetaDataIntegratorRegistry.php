<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Integrator\IntegratorInterface;

class MetaDataIntegratorRegistry implements MetaDataIntegratorRegistryInterface
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @param IntegratorInterface $service
     * @param string                  $identifier
     */
    public function register($service, string $identifier)
    {
        if (!in_array(IntegratorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), IntegratorInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return isset($this->services[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Meta Data Integrator does not exist');
        }

        return $this->services[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->services;
    }
}
