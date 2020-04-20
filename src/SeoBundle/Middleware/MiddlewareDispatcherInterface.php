<?php

namespace SeoBundle\Middleware;

use SeoBundle\Model\SeoMetaDataInterface;

interface MiddlewareDispatcherInterface
{
    /**
     * @param string                     $identifier
     * @param MiddlewareAdapterInterface $middlewareAdapter
     */
    public function registerMiddlewareAdapter(string $identifier, MiddlewareAdapterInterface $middlewareAdapter);

    /**
     * @param string               $identifier
     * @param SeoMetaDataInterface $seoMetaData
     *
     * @return MiddlewareAdapterInterface
     */
    public function buildMiddleware(string $identifier, SeoMetaDataInterface $seoMetaData);

    /**
     * @param callable $callback
     * @param string   $identifier
     */
    public function registerTask(callable $callback, string $identifier);

    /**
     * @param SeoMetaDataInterface $seoMetadata
     */
    public function dispatchTasks(SeoMetaDataInterface $seoMetadata);

    /**
     * @param SeoMetaDataInterface $seoMetadata
     */
    public function dispatchMiddlewareFinisher(SeoMetaDataInterface $seoMetadata);
}

