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

namespace SeoBundle\Controller\Admin;

use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\Model\ElementMetaDataInterface;
use SeoBundle\Tool\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetaDataController extends AdminAbstractController
{
    public function __construct(
        protected ElementMetaDataManagerInterface $elementMetaDataManager,
        protected LocaleProviderInterface $localeProvider
    ) {
    }

    public function getMetaDataDefinitionsAction(): JsonResponse
    {
        return $this->json([
            'configuration' => $this->elementMetaDataManager->getMetaDataIntegratorConfiguration()
        ]);
    }

    /**
     * @throws \Exception
     */
    public function getElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $element = null;
        $availableLocales = null;

        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType');

        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
            $availableLocales = $this->localeProvider->getAllowedLocalesForObject($element);
        } elseif ($elementType === 'document') {
            $element = Document::getById($elementId);
        }

        $configuration = $this->elementMetaDataManager->getMetaDataIntegratorBackendConfiguration($element);
        $elementBackendData = $this->elementMetaDataManager->getElementDataForBackend($elementType, $elementId);

        return $this->adminJson(
            array_merge(
                [
                    'success'          => true,
                    'availableLocales' => $availableLocales,
                    'configuration'    => $configuration,
                ],
                $elementBackendData
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function setElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $elementId = (int) $request->request->get('elementId', 0);
        $elementType = $request->request->get('elementType');
        $task = $request->request->get('task', 'publish');
        $integratorValues = json_decode($request->request->get('integratorValues'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($integratorValues)) {
            return $this->adminJson(['success' => true]);
        }

        foreach ($integratorValues as $integratorName => $integratorData) {
            $sanitizedData = is_array($integratorData) ? $integratorData : [];
            $releaseType = $task === 'publish' ? ElementMetaDataInterface::RELEASE_TYPE_PUBLIC : ElementMetaDataInterface::RELEASE_TYPE_DRAFT;
            $this->elementMetaDataManager->saveElementData($elementType, $elementId, $integratorName, $sanitizedData, false, $releaseType);
        }

        return $this->adminJson([
            'success' => true
        ]);
    }

    /**
     * @throws \Exception
     */
    public function generateMetaDataPreviewAction(Request $request): Response
    {
        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType', '');

        $template = $request->query->get('template', 'none');
        $integratorName = $request->query->get('integratorName');
        $data = json_decode($request->query->get('data', ''), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data)) {
            $data = [];
        }

        $previewData = $this->elementMetaDataManager->generatePreviewDataForElement($elementType, $elementId, $integratorName, $template, $data);

        return $this->render($previewData['path'], $previewData['params']);
    }
}
