<?php

namespace SeoBundle\EventListener\Admin;

use Pimcore\Bundle\AdminBundle\Event\AdminEvents;
use Pimcore\Bundle\SeoBundle\Controller\Document\DocumentController;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\DocumentEvent;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\MetaData\MetaDataProviderInterface;
use SeoBundle\Model\ElementMetaData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SeoDocumentEditorListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected ElementMetaDataManagerInterface $elementMetaDataManager,
        protected MetaDataProviderInterface $metaDataProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminEvents::DOCUMENT_LIST_AFTER_LIST_LOAD => 'adjustData',
            DocumentEvents::POST_UPDATE                => 'updateData',
        ];
    }

    public function adjustData(GenericEvent $event): void
    {
        if (!$event->getSubject() instanceof DocumentController) {
            return;
        }

        $list = $event->getArgument('list');

        foreach ($list['data'] as $listIndex => $item) {

            if ($item['type'] !== 'page') {
                continue;
            }

            $metaData = array_values(
                array_filter(
                    $this->elementMetaDataManager->getElementData('document', $item['id']),
                    static function (ElementMetaData $integratorData) {
                        return $integratorData->getIntegrator() === 'title_description';
                    }
                )
            );

            if (count($metaData) === 0) {
                continue;
            }

            /** @var ElementMetaData $titleDescriptionIntegrator */
            $titleDescriptionIntegrator = $metaData[0];
            $titleDescriptionIntegratorData = $titleDescriptionIntegrator->getData();

            $list['data'][$listIndex]['title'] = $titleDescriptionIntegratorData['title'] ?? null;
            $list['data'][$listIndex]['description'] = $titleDescriptionIntegratorData['description'] ?? null;
        }

        $event->setArgument('list', $list);
    }

    public function updateData(DocumentEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();

        if (!$request instanceof Request) {
            return;
        }

        $document = $event->getDocument();
        $attributes = $request->request->all();
        $hasTitleAttribute = $request->request->has('title');
        $hasDescriptionAttribute = $request->request->has('description');

        // crazy. but there is no other way to determinate, that we're updating via seo panel context...
        if (!$hasTitleAttribute || !$hasDescriptionAttribute || count($attributes) !== 4) {
            return;
        }

        $integratorData = [
            'title'       => $request->request->get('title'),
            'description' => $request->request->get('description')
        ];

        $this->elementMetaDataManager->saveElementData(
            'document',
            $document->getId(),
            'title_description',
            $integratorData
        );

    }
}
