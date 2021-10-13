<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

interface WorkerResponseInterface
{
    public function getStatus(): string;

    public function getMessage(): string;

    public function getQueueEntry(): QueueEntryInterface;

    public function getRawResponse(): mixed;

    public function isDone(): bool;
}
