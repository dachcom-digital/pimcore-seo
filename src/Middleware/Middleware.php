<?php

namespace SeoBundle\Middleware;

class Middleware implements MiddlewareInterface
{
    private string $identifier;
    private MiddlewareDispatcher $middlewareDispatcher;

    public function __construct(string $identifier, MiddlewareDispatcher $middlewareDispatcher)
    {
        $this->identifier = $identifier;
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    public function addTask(callable $callback): void
    {
        $this->middlewareDispatcher->registerTask($callback, $this->identifier);
    }
}
