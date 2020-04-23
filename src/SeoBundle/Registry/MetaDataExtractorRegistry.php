<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Extractor\ExtractorInterface;

class MetaDataExtractorRegistry implements MetaDataExtractorRegistryInterface
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @param ExtractorInterface $service
     * @param string             $identifier
     */
    public function register($service, string $identifier)
    {
        if (!in_array(ExtractorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ExtractorInterface::class, implode(', ', class_implements($service)))
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
            throw new \Exception('"' . $identifier . '" Meta Data Extractor does not exist');
        }

        return $this->services[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return is_array($this->services) ? $this->services : [];
    }
}
