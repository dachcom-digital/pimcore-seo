<?php

namespace SeoBundle\Model;

interface QueueEntryInterface
{
    /**
     * @return string
     */
    public function getUuid();

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $dataId
     */
    public function setDataId($dataId);

    /**
     * @return int
     */
    public function getDataId();

    /**
     * @param string $dataType
     */
    public function setDataType(string $dataType);

    /**
     * @return string
     */
    public function getDataType();

    /**
     * @param string $dataUrl
     */
    public function setDataUrl(string $dataUrl);

    /**
     * @return string
     */
    public function getDataUrl();

    /**
     * @param string $worker
     */
    public function setWorker(string $worker);

    /**
     * @return string
     */
    public function getWorker();

    /**
     * @param string $resourceProcessor
     */
    public function setResourceProcessor(string $resourceProcessor);

    /**
     * @return string
     */
    public function getResourceProcessor();

    /**
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date);

    /**
     * @return \DateTime
     */
    public function getCreationDate();

}
