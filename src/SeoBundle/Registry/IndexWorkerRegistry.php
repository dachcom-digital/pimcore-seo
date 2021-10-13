<?php

namespace SeoBundle\Registry;

use SeoBundle\Worker\IndexWorkerInterface;

class IndexWorkerRegistry implements IndexWorkerRegistryInterface
{
    protected array $services = [];

    public function register(mixed $service, string $identifier): void
    {
        if (!in_array(IndexWorkerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), IndexWorkerInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    public function has($identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get($identifier): IndexWorkerInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Index Worker does not exist');
        }

        return $this->services[$identifier];
    }

    public function getAll(): array
    {
        return is_array($this->services) ? $this->services : [];
    }
}
