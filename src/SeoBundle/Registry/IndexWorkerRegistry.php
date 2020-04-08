<?php

namespace SeoBundle\Registry;

use SeoBundle\Worker\IndexWorkerInterface;

class IndexWorkerRegistry implements IndexWorkerRegistryInterface
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @param IndexWorkerInterface $service
     * @param string               $identifier
     */
    public function register($service, $identifier)
    {
        if (!in_array(IndexWorkerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), IndexWorkerInterface::class, implode(', ', class_implements($service)))
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
            throw new \Exception('"' . $identifier . '" Index Worker does not exist');
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
