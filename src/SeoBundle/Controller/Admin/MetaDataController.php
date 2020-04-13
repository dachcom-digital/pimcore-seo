<?php

namespace SeoBundle\Controller\Admin;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MetaDataController extends AdminController
{
    /**
     * @var array
     */
    protected $integratorConfiguration;

    /**
     * @var ElementMetaDataManagerInterface
     */
    protected $elementMetaDataManager;

    /**
     * @var MetaDataIntegratorRegistryInterface
     */
    protected $metaDataIntegratorRegistry;

    /**
     * @param array                               $integratorConfiguration
     * @param ElementMetaDataManagerInterface     $elementMetaDataManager
     * @param MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
     */
    public function __construct(
        array $integratorConfiguration,
        ElementMetaDataManagerInterface $elementMetaDataManager,
        MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
    ) {
        $this->integratorConfiguration = $integratorConfiguration;
        $this->elementMetaDataManager = $elementMetaDataManager;
        $this->metaDataIntegratorRegistry = $metaDataIntegratorRegistry;
    }

    /**
     * @return string
     */
    public function getMetaDataDefinitionsAction()
    {
        return $this->json([
            'configuration' => $this->integratorConfiguration
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
        $data = [];
        $configuration = [];
        $element = null;

        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType');

        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
        } elseif ($elementType === 'document') {
            $element = Document::getById($elementId);
        }

        foreach ($this->integratorConfiguration['enabled_integrator'] as $enabledIntegratorName) {
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->has($enabledIntegratorName) ? $this->metaDataIntegratorRegistry->get($enabledIntegratorName) : null;
            $config = $metaDataIntegrator === null ? [] : $metaDataIntegrator->getBackendConfiguration($element);
            $configuration[$enabledIntegratorName] = $config;
        }

        foreach ($this->elementMetaDataManager->getElementData($elementType, $elementId) as $element) {
            $data[$element->getIntegrator()] = $element->getData();
        }

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
}
