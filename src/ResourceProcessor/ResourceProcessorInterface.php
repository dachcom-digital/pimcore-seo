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

namespace SeoBundle\ResourceProcessor;

use SeoBundle\Exception\WorkerResponseInterceptException;
use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Worker\WorkerResponseInterface;

interface ResourceProcessorInterface
{
    public function supportsWorker(string $workerIdentifier): bool;

    public function supportsResource(mixed $resource): bool;

    public function generateQueueContext(mixed $resource): mixed;

    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, array $context, mixed $resource): ?QueueEntryInterface;

    /**
     * @throws \Exception
     * @throws WorkerResponseInterceptException
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}
