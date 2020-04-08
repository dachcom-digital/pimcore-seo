<?php

namespace SeoBundle\Registry;

use SeoBundle\ResourceProcessor\ResourceProcessorInterface;

class ResourceProcessorRegistry implements ResourceProcessorRegistryInterface
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @param ResourceProcessorInterface $service
     * @param string                     $identifier
     */
    public function register($service, string $identifier)
    {
        if (!in_array(ResourceProcessorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ResourceProcessorInterface::class, implode(', ', class_implements($service)))
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
            throw new \Exception('"' . $identifier . '" Resource Processor does not exist');
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
