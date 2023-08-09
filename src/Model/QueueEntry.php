<?php

namespace SeoBundle\Model;

class QueueEntry implements QueueEntryInterface
{
    protected string $uuid;
    protected string $type;
    protected string $dataType;
    protected int $dataId;
    protected string $dataUrl;
    protected string $worker;
    protected string $resourceProcessor;
    protected \DateTime $creationDate;

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setDataId($dataId): void
    {
        $this->dataId = $dataId;
    }

    public function getDataId(): int
    {
        return $this->dataId;
    }

    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function setDataUrl(string $dataUrl): void
    {
        $this->dataUrl = $dataUrl;
    }

    public function getDataUrl(): string
    {
        return $this->dataUrl;
    }

    public function setWorker(string $worker): void
    {
        $this->worker = $worker;
    }

    public function getWorker(): string
    {
        return $this->worker;
    }

    public function setResourceProcessor(string $resourceProcessor): void
    {
        $this->resourceProcessor = $resourceProcessor;
    }

    public function getResourceProcessor(): string
    {
        return $this->resourceProcessor;
    }

    public function setCreationDate(\DateTime $date): void
    {
        $this->creationDate = $date;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }
}
