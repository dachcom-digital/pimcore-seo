<?php

namespace SeoBundle\Command;

use SeoBundle\Queue\QueueDataProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'seo:check-index-queue',
    description: 'For internal use only',
    hidden: true,
)]
class QueuedIndexDataCommand extends Command
{
    public function __construct(protected QueueDataProcessorInterface $dataProcessor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataProcessor->process([]);

        return self::SUCCESS;
    }
}
