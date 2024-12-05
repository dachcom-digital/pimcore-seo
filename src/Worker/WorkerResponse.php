<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

class WorkerResponse implements WorkerResponseInterface
{
    protected int $status;
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

    public function getStatus(): int
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
