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
