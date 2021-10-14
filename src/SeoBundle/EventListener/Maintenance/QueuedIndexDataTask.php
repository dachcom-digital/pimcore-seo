<?php

namespace SeoBundle\EventListener\Maintenance;

use Pimcore\Maintenance\TaskInterface;
use SeoBundle\Queue\QueueDataProcessorInterface;

class QueuedIndexDataTask implements TaskInterface
{
    protected QueueDataProcessorInterface $dataProcessor;

    public function __construct(QueueDataProcessorInterface $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
    }

    public function execute(): void
    {
        $this->dataProcessor->process([]);
    }
}
