<?php

namespace SeoBundle\EventListener\Maintenance;

use Pimcore\Maintenance\TaskInterface;
use SeoBundle\Queue\QueueDataProcessorInterface;

class QueuedIndexDataTask implements TaskInterface
{
    /**
     * @var QueueDataProcessorInterface
     */
    protected $dataProcessor;

    /**
     * @param QueueDataProcessorInterface $dataProcessor
     */
    public function __construct(QueueDataProcessorInterface $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->dataProcessor->process([]);
    }
}
