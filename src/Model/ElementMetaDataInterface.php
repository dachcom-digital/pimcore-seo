<?php

namespace SeoBundle\Model;

interface ElementMetaDataInterface
{
    public const RELEASE_TYPE_PUBLIC = 'public';
    public const RELEASE_TYPE_DRAFT = 'draft';

    public function getId(): ?int;

    public function setElementType(string $elementType): void;

    public function getElementType(): string;

    public function setElementId(int $elementId): void;

    public function getElementId(): int;

    public function setIntegrator(string $integrator): void;

    public function getIntegrator(): string;

    public function setData(array $data): void;

    public function getData(): array;

    public function getReleaseType(): string;

    public function setReleaseType(string $releaseType): void;
}
