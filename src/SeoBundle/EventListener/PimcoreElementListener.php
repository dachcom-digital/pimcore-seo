<?php

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
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var QueueManagerInterface
     */
    protected $queueManager;

    /**
     * @param bool                  $enabled
     * @param QueueManagerInterface $queueManager
     */
    public function __construct(bool $enabled, QueueManagerInterface $queueManager)
    {
        $this->enabled = $enabled;
        $this->queueManager = $queueManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
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

    /**
     * @param DocumentEvent $event
     */
    public function onDocumentPostUpdate(DocumentEvent $event)
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

    /**
     * @param DocumentEvent $event
     */
    public function onDocumentPreDelete(DocumentEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        if ($event->getDocument()->getType() !== 'page') {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getDocument());
    }

    /**
     * @param DataObjectEvent $event
     */
    public function onObjectPostUpdate(DataObjectEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        /** @var Concrete $object */
        $object = $event->getObject();

        $dispatchType = method_exists($object, 'isPublished')
            ? $object->isPublished() === false
                ? IndexWorkerInterface::TYPE_DELETE
                : IndexWorkerInterface::TYPE_UPDATE
            : IndexWorkerInterface::TYPE_UPDATE;

        $this->queueManager->addToQueue($dispatchType, $event->getObject());
    }

    /**
     * @param DataObjectEvent $event
     */
    public function onObjectPreDelete(DataObjectEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getObject());
    }

    /**
     * @param AssetEvent $event
     */
    public function onAssetPostAdd(AssetEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_ADD, $event->getAsset());
    }

    /**
     * @param AssetEvent $event
     */
    public function onAssetPostUpdate(AssetEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_UPDATE, $event->getAsset());
    }

    /**
     * @param AssetEvent $event
     */
    public function onAssetPreDelete(AssetEvent $event)
    {
        if ($this->enabled === false) {
            return;
        }

        $this->queueManager->addToQueue(IndexWorkerInterface::TYPE_DELETE, $event->getAsset());
    }
}
