<?php

namespace SeoBundle\Model;

use SeoBundle\Middleware\MiddlewareInterface;

interface SeoMetaDataInterface
{
    /**
     * @throws \Exception
     */
    public function getMiddleware(string $middlewareAdapterName): MiddlewareInterface;

    public function setMetaDescription(string $metaDescription): void;

    public function getMetaDescription(): string;

    public function setOriginalUrl(string $originalUrl): void;

    public function getOriginalUrl(): string;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function setExtraProperties(array|\Traversable $extraProperties): void;

    public function setExtraNames(array|\Traversable $extraNames): void;

    public function setExtraHttp(array|\Traversable $extraHttp): void;

    public function getExtraProperties(): array;

    public function getExtraNames(): array;

    public function getExtraHttp(): array;

    public function addExtraProperty(string $key, string $value);

    public function addExtraName(string $key, string $value);

    public function addExtraHttp(string $key, string $value);

    public function getSchema(): array;

    public function addSchema(array $schemaJsonLd): void;

    /**
     * Do not use this method!
     * It's required to allow a seamless migration from old pimcore installations.
     *
     * @internal
     * @deprecated
     */
    public function getRaw(): array;

    /**
     * Do not use this method!
     * It's required to allow a seamless migration from old pimcore installations.
     *
     * @internal
     * @deprecated
     */
    public function addRaw(string $value): void;
}
