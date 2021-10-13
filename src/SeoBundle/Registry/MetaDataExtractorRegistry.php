<?php

namespace SeoBundle\Registry;

use SeoBundle\MetaData\Extractor\ExtractorInterface;

class MetaDataExtractorRegistry implements MetaDataExtractorRegistryInterface
{
    protected array $services = [];

    public function register(mixed $service, string $identifier): void
    {
        if (!in_array(ExtractorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ExtractorInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    public function has($identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get($identifier): ExtractorInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Meta Data Extractor does not exist');
        }

        return $this->services[$identifier];
    }

    public function getAll(): array
    {
        return is_array($this->services) ? $this->services : [];
    }
}
