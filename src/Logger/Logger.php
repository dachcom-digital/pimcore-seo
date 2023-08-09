<?php

namespace SeoBundle\Logger;

use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;

class Logger implements LoggerInterface
{
    public function __construct(protected ApplicationLogger $applicationLogger)
    {
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->getLogger()->log($level, $message, $context);
    }

    protected function getLogger(): ApplicationLogger
    {
        return $this->applicationLogger::getInstance('seo-bundle', true);
    }
}
