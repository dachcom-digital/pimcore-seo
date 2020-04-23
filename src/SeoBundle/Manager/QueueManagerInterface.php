<?php

namespace SeoBundle\Manager;

interface QueueManagerInterface
{
    /**
     * Send data to queue.
     *
     * @param string $processType
     * @param mixed  $resource
     */
    public function addToQueue(string $processType, $resource);

    /**
     * Send QueueEntries to enabled index provider workers.
     */
    public function processQueue();
}
