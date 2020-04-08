<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

interface IndexWorkerInterface
{
    const TYPE_ADD = 'add';

    const TYPE_UPDATE = 'update';

    const TYPE_DELETE = 'delete';

    /**
     * @param array $configuration
     *
     * @throws \Exception
     */
    public function setConfiguration(array $configuration);

    /**
     * @param QueueEntryInterface[] $queueEntries
     * @param array                 $resultCallBack
     *
     * @throws \Exception
     */
    public function process(array $queueEntries, array $resultCallBack);
}