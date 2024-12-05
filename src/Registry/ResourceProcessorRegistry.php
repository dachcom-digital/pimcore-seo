<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\Registry;

use SeoBundle\ResourceProcessor\ResourceProcessorInterface;

class ResourceProcessorRegistry implements ResourceProcessorRegistryInterface
{
    protected array $services = [];

    public function register(mixed $service, string $identifier): void
    {
        if (!in_array(ResourceProcessorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ResourceProcessorInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    public function has($identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get($identifier): ResourceProcessorInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Resource Processor does not exist');
        }

        return $this->services[$identifier];
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
