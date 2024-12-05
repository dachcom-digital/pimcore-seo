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

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    protected array $middlewareAdapterStack;
    protected array $middleware;
    protected array $tasks;

    public function __construct()
    {
        $this->middleware = [];
        $this->middlewareAdapterStack = [];
        $this->tasks = [];
    }

    public function registerMiddlewareAdapter(string $identifier, MiddlewareAdapterInterface $middlewareAdapter): void
    {
        $this->middlewareAdapterStack[$identifier] = $middlewareAdapter;
    }

    public function buildMiddleware(string $identifier, SeoMetaDataInterface $seoMetaData): MiddlewareInterface
    {
        if (!isset($this->middlewareAdapterStack[$identifier])) {
            throw new \Exception(sprintf('SEO MetaData middleware "%s" not registered.', $identifier));
        }

        if (isset($this->middleware[$identifier])) {
            return $this->middleware[$identifier];
        }

        $this->middlewareAdapterStack[$identifier]->boot();
        $this->middleware[$identifier] = new Middleware($identifier, $this);

        return $this->middleware[$identifier];
    }

    public function registerTask(callable $callback, string $identifier): void
    {
        $this->tasks[] = [
            'identifier' => $identifier,
            'callback'   => $callback
        ];
    }

    public function dispatchTasks(SeoMetaDataInterface $seoMetadata): void
    {
        foreach ($this->tasks as $immediateTask) {
            $middlewareAdapter = $this->middlewareAdapterStack[$immediateTask['identifier']];
            call_user_func_array($immediateTask['callback'], array_merge([$seoMetadata], $middlewareAdapter->getTaskArguments()));
        }

        // reset tasks for next loop.
        $this->tasks = [];
    }

    public function dispatchMiddlewareFinisher(SeoMetaDataInterface $seoMetadata): void
    {
        foreach ($this->middleware as $identifier => $middleware) {
            $middlewareAdapter = $this->middlewareAdapterStack[$identifier];
            $middlewareAdapter->onFinish($seoMetadata);
        }
    }
}
