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

namespace SeoBundle\EventListener;

use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Model\DataObject\Concrete;
use SeoBundle\Manager\QueueManagerInterface;
use SeoBundle\Worker\IndexWorkerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PimcoreElementListener implements EventSubscriberInterface
{
    public function __construct(
        protected bool $enabled,
        protected QueueManagerInterface $queueManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::POST_UPDATE   => 'onDocumentPostUpdate',
            DocumentEvents::PRE_DELETE    => 'onDocumentPreDelete',
            DataObjectEvents::POST_UPDATE => 'onObjectPostUpdate',
            DataObjectEvents::PRE_DELETE  => 'onObjectPreDelete',
            AssetEvents::POST_ADD         => 'onAssetPostAdd',
            AssetEvents::POST_UPDATE      => 'onAssetPostUpdate',
            AssetEvents::PRE_DELETE       => 'onAssetPreDelete',
        ];
    }

    public function onDocumentPostUpdate(DocumentEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        if ($event->getDocument()->getType() !== 'page') {
            return;
        }

        $dispatchType = $event->getDocument()->isPublished() === false
            ? IndexWorkerInterface::TYPE_DELETE
            : IndexWorkerInterface::TYPE_UPDATE;

        $this->queueManager->addToQueue($dispatchType, $event->getDocument());
    }

    public function onDocumentPreDelete(DocumentEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        if ($event->getDocument()->getType() !== 'page') {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getDocument());
    }

    public function onObjectPostUpdate(DataObjectEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        $object = $event->getObject();
        if (!$object instanceof Concrete) {
            return;
        }

        $dispatchType = $object->isPublished() === false
            ? IndexWorkerInterface::TYPE_DELETE
            : IndexWorkerInterface::TYPE_UPDATE;

        $this->queueManager->addToQueue($dispatchType, $event->getObject());
    }

    public function onObjectPreDelete(DataObjectEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getObject());
    }

    public function onAssetPostAdd(AssetEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_ADD, $event->getAsset());
    }

    public function onAssetPostUpdate(AssetEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_UPDATE, $event->getAsset());
    }

    public function onAssetPreDelete(AssetEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getAsset());
    }
}
