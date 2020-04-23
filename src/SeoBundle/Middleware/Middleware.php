<?php

namespace SeoBundle\Middleware;

class Middleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var MiddlewareDispatcher
     */
    private $middlewareDispatcher;

    /**
     * @param string               $identifier
     * @param MiddlewareDispatcher $middlewareDispatcher
     */
    public function __construct(string $identifier, MiddlewareDispatcher $middlewareDispatcher)
    {
        $this->identifier = $identifier;
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function addTask($callback)
    {
        $this->middlewareDispatcher->registerTask($callback, $this->identifier);
    }
}
