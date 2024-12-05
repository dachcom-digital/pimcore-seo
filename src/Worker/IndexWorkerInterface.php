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
use Symfony\Component\OptionsResolver\OptionsResolver;

interface IndexWorkerInterface
{
    public const TYPE_ADD = 'add';
    public const TYPE_UPDATE = 'update';
    public const TYPE_DELETE = 'delete';

    /**
     * Return true or false|string.
     * If string gets returned, it same as return false but will be added to logs.
     */
    public function canProcess(): string|bool;

    /**
     * @param QueueEntryInterface[] $queueEntries
     * @param array                 $resultCallBack
     *
     * @throws \Exception
     */
    public function process(array $queueEntries, array $resultCallBack): void;

    public function setConfiguration(array $configuration): void;

    public static function configureOptions(OptionsResolver $resolver): void;
}
