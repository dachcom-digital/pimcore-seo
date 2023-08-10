<?php

namespace SeoBundle\Middleware;

use SeoBundle\Model\SeoMetaDataInterface;

interface MiddlewareDispatcherInterface
{
    public function registerMiddlewareAdapter(string $identifier, MiddlewareAdapterInterface $middlewareAdapter): void;

    public function buildMiddleware(string $identifier, SeoMetaDataInterface $seoMetaData): MiddlewareInterface;

    public function registerTask(callable $callback, string $identifier): void;

    public function dispatchTasks(SeoMetaDataInterface $seoMetadata): void;

    public function dispatchMiddlewareFinisher(SeoMetaDataInterface $seoMetadata): void;
}
