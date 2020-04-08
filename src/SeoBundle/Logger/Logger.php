<?php

namespace SeoBundle\Logger;

use Pimcore\Log\ApplicationLogger;

class Logger implements LoggerInterface
{
    /**
     * @var ApplicationLogger
     */
    protected $applicationLogger;

    /**
     * @param ApplicationLogger $applicationLogger
     */
    public function __construct(ApplicationLogger $applicationLogger)
    {
        $this->applicationLogger = $applicationLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->getLogger()->log($level, $message, $context);
    }

    /**
     * @return ApplicationLogger
     */
    protected function getLogger()
    {
        return $this->applicationLogger::getInstance('seo-bundle', true);
    }

}
