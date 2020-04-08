<?php

namespace SeoBundle\Logger;

interface LoggerInterface
{
    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = []);
}
