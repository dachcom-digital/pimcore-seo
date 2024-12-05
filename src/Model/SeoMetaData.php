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

namespace SeoBundle\Model;

use SeoBundle\Middleware\MiddlewareDispatcherInterface;
use SeoBundle\Middleware\MiddlewareInterface;

class SeoMetaData implements SeoMetaDataInterface
{
    private MiddlewareDispatcherInterface $middlewareDispatcher;
    private int $id;
    private string $originalUrl;
    private string $metaDescription = '';
    private string $title = '';
    private array $extraProperties = [];
    private array $extraNames = [];
    private array $extraHttp = [];
    private array $schema = [];

    /**
     * @deprecated
     */
    private array $raw = [];

    public function __construct(MiddlewareDispatcherInterface $middlewareDispatcher)
    {
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMiddleware(string $middlewareAdapterName): MiddlewareInterface
    {
        return $this->middlewareDispatcher->buildMiddleware($middlewareAdapterName, $this);
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setOriginalUrl(string $originalUrl): void
    {
        $this->originalUrl = $originalUrl;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setExtraProperties(array|\Traversable $extraProperties): void
    {
        $this->extraProperties = $this->toArray($extraProperties);
    }

    public function getExtraProperties(): array
    {
        return $this->extraProperties;
    }

    public function addExtraProperty($key, $value): void
    {
        $this->extraProperties[$key] = (string) $value;
    }

    public function removeExtraProperty($key): void
    {
        if (array_key_exists($key, $this->extraProperties)) {
            unset($this->extraProperties[$key]);
        }
    }

    public function setExtraNames(array|\Traversable $extraNames): void
    {
        $this->extraNames = $this->toArray($extraNames);
    }

    public function getExtraNames(): array
    {
        return $this->extraNames;
    }

    public function addExtraName(string $key, string $value): void
    {
        $this->extraNames[$key] = $value;
    }

    public function removeExtraName(string $key): void
    {
        if (array_key_exists($key, $this->extraNames)) {
            unset($this->extraNames[$key]);
        }
    }

    public function setExtraHttp(array|\Traversable $extraHttp): void
    {
        $this->extraHttp = $this->toArray($extraHttp);
    }

    public function getExtraHttp(): array
    {
        return $this->extraHttp;
    }

    public function addExtraHttp(string $key, string $value): void
    {
        $this->extraHttp[$key] = (string) $value;
    }

    public function removeExtraHttp(string $key): void
    {
        if (array_key_exists($key, $this->extraHttp)) {
            unset($this->extraHttp[$key]);
        }
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function addSchema(array $schemaJsonLd): void
    {
        $this->schema[] = $schemaJsonLd;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }

    public function addRaw(string $value): void
    {
        $this->raw[] = $value;
    }

    private function toArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data);
        }

        throw new \InvalidArgumentException(
            sprintf('Expected array or Traversable, got "%s"', is_object($data) ? get_class($data) : gettype($data))
        );
    }
}
