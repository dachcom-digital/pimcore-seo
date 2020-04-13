<?php

namespace SeoBundle\Model;

interface ElementMetaDataInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $elementType
     */
    public function setElementType(string $elementType);

    /**
     * @return string
     */
    public function getElementType();

    /**
     * @param int $elementId
     */
    public function setElementId(int $elementId);

    /**
     * @return int
     */
    public function getElementId();

    /**
     * @param string $integrator
     */
    public function setIntegrator(string $integrator);

    /**
     * @return string
     */
    public function getIntegrator();

    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

}
