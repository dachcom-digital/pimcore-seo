<?php

namespace SeoBundle\Middleware;

use SeoBundle\Model\SeoMetaDataInterface;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * @var MiddlewareAdapterInterface[]
     */
    protected $middlewareAdapterStack;

    /**
     * @var array
     */
    protected $middleware;

    /**
     * @var array
     */
    protected $tasks;

    public function __construct()
    {
        $this->middleware = [];
        $this->middlewareAdapterStack = [];
        $this->tasks = [];
    }

    /**
     * {@inheritdoc}
     */
    public function registerMiddlewareAdapter(string $identifier, MiddlewareAdapterInterface $middlewareAdapter)
    {
        $this->middlewareAdapterStack[$identifier] = $middlewareAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMiddleware(string $identifier, SeoMetaDataInterface $seoMetaData)
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

    /**
     * {@inheritDoc}
     */
    public function registerTask(callable $callback, string $identifier)
    {
        if (!is_callable($callback)) {
            return;
        }

        $this->tasks[] = [
            'identifier' => $identifier,
            'callback'   => $callback
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchTasks(SeoMetaDataInterface $seoMetadata)
    {
        foreach ($this->tasks as $immediateTask) {
            $middlewareAdapter = $this->middlewareAdapterStack[$immediateTask['identifier']];
            call_user_func_array($immediateTask['callback'], array_merge([$seoMetadata], $middlewareAdapter->getTaskArguments()));
        }

        // reset tasks for next loop.
        $this->tasks = [];
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchMiddlewareFinisher(SeoMetaDataInterface $seoMetadata)
    {
        foreach ($this->middleware as $identifier => $middleware) {
            $middlewareAdapter = $this->middlewareAdapterStack[$identifier];
            $middlewareAdapter->onFinish($seoMetadata);
        }
    }
}
