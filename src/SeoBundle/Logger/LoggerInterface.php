<?php

namespace SeoBundle\Logger;

interface LoggerInterface
{
    public function log(string $level, string $message, array $context = []): void;
}
