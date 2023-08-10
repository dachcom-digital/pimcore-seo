<?php

namespace SeoBundle\EventListener\Maintenance;

use Pimcore\Maintenance\TaskInterface;
use SeoBundle\Queue\QueueDataProcessorInterface;

class QueuedIndexDataTask implements TaskInterface
{
    public function __construct(protected QueueDataProcessorInterface $dataProcessor)
    {
    }

    public function execute(): void
    {
        $this->dataProcessor->process([]);
    }
}
