<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

class WorkerResponse implements WorkerResponseInterface
{
    protected string $status;
    protected string $message;
    protected bool $successFullyProcessed;
    protected QueueEntryInterface $queueEntry;
    protected mixed $rawResponse;

    public function __construct(int $status, ?string $message, bool $successFullyProcessed, QueueEntryInterface $queueEntry, mixed $rawResponse)
    {
        $this->status = $status;
        $this->message = $message;
        $this->successFullyProcessed = $successFullyProcessed;
        $this->queueEntry = $queueEntry;
        $this->rawResponse = $rawResponse;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getQueueEntry(): QueueEntryInterface
    {
        return $this->queueEntry;
    }

    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    public function isDone(): bool
    {
        return $this->successFullyProcessed === true;
    }
}
