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
    public function __construct(protected ElementMetaDataManagerInterface $elementMetaDataManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_DELETE => 'handleObjectDeletion',
            DocumentEvents::PRE_DELETE   => 'handleDocumentDeletion',
        ];
    }

    public function handleDocumentDeletion(DocumentEvent $event): void
    {
        $this->elementMetaDataManager->deleteElementData('document', $event->getDocument()->getId(), null);
    }

    public function handleObjectDeletion(DataObjectEvent $event): void
    {
        $this->elementMetaDataManager->deleteElementData('object', $event->getObject()->getId(), null);
    }
}
