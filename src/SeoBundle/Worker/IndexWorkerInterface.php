<?php

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
