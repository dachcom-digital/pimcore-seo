<?php

namespace SeoBundle\EventListener;

use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ElementMetaDataListener implements EventSubscriberInterface
{
    /**
     * @var ElementMetaDataManagerInterface
     */
    protected $elementMetaDataManager;

    /**
     * @param ElementMetaDataManagerInterface $elementMetaDataManager
     */
    public function __construct(ElementMetaDataManagerInterface $elementMetaDataManager)
    {
        $this->elementMetaDataManager = $elementMetaDataManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_DELETE => 'handleObjectDeletion',
            DocumentEvents::PRE_DELETE   => 'handleDocumentDeletion',
        ];
    }

    /**
     * @param DocumentEvent $event
     */
    public function handleDocumentDeletion(DocumentEvent $event)
    {
        $this->elementMetaDataManager->deleteElementData('document', $event->getDocument()->getId());
    }

    /**
     * @param DataObjectEvent $event
     */
    public function handleObjectDeletion(DataObjectEvent $event)
    {
        $this->elementMetaDataManager->deleteElementData('object', $event->getObject()->getId());
    }
}
