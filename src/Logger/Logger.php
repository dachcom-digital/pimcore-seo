<?php

namespace SeoBundle\Logger;

class Logger implements LoggerInterface
{
    public function __construct(protected \Psr\Log\LoggerInterface $logger)
    {
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
