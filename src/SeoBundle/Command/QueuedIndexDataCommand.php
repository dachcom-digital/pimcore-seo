<?php

namespace SeoBundle\Command;

use SeoBundle\Queue\QueueDataProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueuedIndexDataCommand extends Command
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
        parent::__construct();
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setName('seo:check-index-queue')
            ->setDescription('For internal use only');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dataProcessor->process([]);

        return 0;
    }
}
