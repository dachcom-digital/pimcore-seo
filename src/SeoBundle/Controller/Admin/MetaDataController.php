<?php

namespace SeoBundle\Controller\Admin;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetaDataController extends AdminController
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
     * @return string
     */
    public function getMetaDataDefinitionsAction()
    {
        return $this->json([
            'configuration' => $this->elementMetaDataManager->getMetaDataIntegratorConfiguration()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function getElementMetaDataConfigurationAction(Request $request)
    {
        $element = null;

        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType');

        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
        } elseif ($elementType === 'document') {
            $element = Document::getById($elementId);
        }

        $configuration = $this->elementMetaDataManager->getMetaDataIntegratorBackendConfiguration($element);
        $data = $this->elementMetaDataManager->getElementDataForBackend($elementType, $elementId);

        return $this->adminJson([
            'success'       => true,
            'data'          => $data,
            'configuration' => $configuration,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function setElementMetaDataConfigurationAction(Request $request)
    {
        $elementId = (int) $request->request->get('elementId', 0);
        $elementType = $request->request->get('elementType');
        $integratorValues = json_decode($request->request->get('integratorValues'), true);

        if (!is_array($integratorValues)) {
            return $this->adminJson(['success' => true]);
        }

        foreach ($integratorValues as $integratorName => $integratorData) {
            $sanitizedData = is_array($integratorData) ? $integratorData : [];
            $this->elementMetaDataManager->saveElementData($elementType, $elementId, $integratorName, $sanitizedData);
        }

        return $this->adminJson([
            'success' => true
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function generateMetaDataPreviewAction(Request $request)
    {
        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType', '');

        $template = $request->query->get('template', 'none');
        $integratorName = $request->query->get('integratorName');
        $data = json_decode($request->query->get('data', ''), true);

        if (empty($data)) {
            $data = [];
        }

        $previewData = $this->elementMetaDataManager->generatePreviewDataForElement($elementType, $elementId, $integratorName, $template, $data);

        return $this->render($previewData['path'], $previewData['params']);
    }
}
