<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

interface WorkerResponseInterface
{
    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return QueueEntryInterface
     */
    public function getQueueEntry();

    /**
     * @return mixed
     */
    public function getRawResponse();

    /**
     * @return bool
     */
    public function isDone();
}
