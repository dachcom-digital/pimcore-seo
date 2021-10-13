<?php

namespace SeoBundle\Middleware;

interface MiddlewareInterface
{
    public function addTask(callable $callback): void;
}
