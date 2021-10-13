<?php

namespace SeoBundle\Middleware;

use SeoBundle\Model\SeoMetaDataInterface;

interface MiddlewareAdapterInterface
{
    /**
     * Boot your middleware.
     * This method gets called once per request - if middleware gets involved at all!
     */
    public function boot(): void;

    /**
     * Array with all arguments to inject them into a task.
     */
    public function getTaskArguments(): array;

    /**
     * This method gets executed after all extractors have been called.
     *
     * Within this method you'll receive the populated SeoMetaDataInterface class.
     *
     * After all extractors have been dispatched, the called adapter is allowed to modify the SeoMetaDataInterface
     * before its data gets appended to the documents head.
     */
    public function onFinish(SeoMetaDataInterface $seoMetadata): void;
}
