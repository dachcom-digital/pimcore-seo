<?php

namespace SeoBundle\Model;

class QueueEntry implements QueueEntryInterface
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var int
     */
    protected $dataId;

    /**
     * @var string
     */
    protected $dataUrl;

    /**
     * @var string
     */
    protected $worker;

    /**
     * @var string
     */
    protected $resourceProcessor;

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * {@inheritdoc}
     */
    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataId($dataId)
    {
        $this->dataId = $dataId;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataType(string $dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataUrl(string $dataUrl)
    {
        $this->dataUrl = $dataUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataUrl()
    {
        return $this->dataUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setWorker(string $worker)
    {
        $this->worker = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceProcessor(string $resourceProcessor)
    {
        $this->resourceProcessor = $resourceProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceProcessor()
    {
        return $this->resourceProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->creationDate = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}
