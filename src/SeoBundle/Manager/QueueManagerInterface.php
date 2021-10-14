<?php

namespace SeoBundle\Manager;

interface QueueManagerInterface
{
    public function addToQueue(string $processType, mixed $resource): void;

    /**
     * Send QueueEntries to enabled index provider workers.
     */
    public function processQueue(): void;
}
