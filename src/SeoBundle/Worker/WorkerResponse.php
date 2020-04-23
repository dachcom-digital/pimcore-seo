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
     * @param mixed               $rawResponse
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
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueEntry()
    {
        return $this->queueEntry;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function isDone()
    {
        return $this->successFullyProcessed === true;
    }
}
