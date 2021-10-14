<?php

namespace SeoBundle\Logger;

use Pimcore\Log\ApplicationLogger;

class Logger implements LoggerInterface
{
    protected ApplicationLogger $applicationLogger;

    public function __construct(ApplicationLogger $applicationLogger)
    {
        $this->applicationLogger = $applicationLogger;
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
