<?php

namespace SeoBundle\Command;

use SeoBundle\Queue\QueueDataProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueuedIndexDataCommand extends Command
{
    protected static $defaultName = 'seo:check-index-queue';
    protected static $defaultDescription = 'For internal use only';

    protected QueueDataProcessorInterface $dataProcessor;

    public function __construct(QueueDataProcessorInterface $dataProcessor)
    {
        parent::__construct();
        $this->dataProcessor = $dataProcessor;
    }

    protected function configure(): void
    {
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataProcessor->process([]);

        return 0;
    }
}
