<?php

namespace SeoBundle\Middleware;

interface MiddlewareInterface
{
    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function addTask($callback);
}
