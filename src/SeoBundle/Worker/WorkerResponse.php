<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

class WorkerResponse implements WorkerResponseInterface
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var bool
     */
    protected $successFullyProcessed;

    /**
     * @var QueueEntryInterface
     */
    protected $queueEntry;

    /**
     * @var mixed
     */
    protected $rawResponse;

    /**
     * @param int                 $status
     * @param string              $message
     * @param bool                $successFullyProcessed
     * @param QueueEntryInterface $queueEntry
     * @param                     $rawResponse
     */
    public function __construct(int $status, ?string $message, bool $successFullyProcessed, QueueEntryInterface $queueEntry, $rawResponse)
    {
        $this->status = $status;
        $this->message = $message;
        $this->successFullyProcessed = $successFullyProcessed;
        $this->queueEntry = $queueEntry;
        $this->rawResponse = $rawResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueueEntry()
    {
        return $this->queueEntry;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function isDone()
    {
        return $this->successFullyProcessed === true;
    }
}