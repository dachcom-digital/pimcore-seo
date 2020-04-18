<?php

namespace SeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface IndexWorkerInterface
{
    const TYPE_ADD = 'add';

    const TYPE_UPDATE = 'update';

    const TYPE_DELETE = 'delete';

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration);

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver);

    /**
     * @param QueueEntryInterface[] $queueEntries
     * @param array                 $resultCallBack
     *
     * @throws \Exception
     */
    public function process(array $queueEntries, array $resultCallBack);
}