<?php

namespace SeoBundle\Model;

class ElementMetaData implements ElementMetaDataInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $elementType;

    /**
     * @var int
     */
    protected $elementId;

    /**
     * @var string
     */
    protected $integrator;

    /**
     * @var array
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setElementType(string $elementType)
    {
        $this->elementType = $elementType;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementType()
    {
        return $this->elementType;
    }

    /**
     * {@inheritdoc}
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * {@inheritdoc}
     */
    public function setIntegrator(string $integrator)
    {
        $this->integrator = $integrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrator()
    {
        return $this->integrator;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }
}
