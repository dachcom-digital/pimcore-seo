<?php

namespace SeoBundle\Model;

interface QueueEntryInterface
{
    public function getUuid(): string;

    public function setType(string $type): void;

    public function getType(): string;

    public function setDataId(int $dataId): void;

    public function getDataId(): int;

    public function setDataType(string $dataType): void;

    public function getDataType(): string;

    public function setDataUrl(string $dataUrl): void;

    public function getDataUrl(): string;

    public function setWorker(string $worker): void;

    public function getWorker(): string;

    public function setResourceProcessor(string $resourceProcessor): void;

    public function getResourceProcessor(): string;

    public function setCreationDate(\DateTime $date): void;

    public function getCreationDate(): \DateTime;
}
