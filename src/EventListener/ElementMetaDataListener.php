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

use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
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
