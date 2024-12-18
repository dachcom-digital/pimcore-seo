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

namespace SeoBundle\Model;

class ElementMetaData implements ElementMetaDataInterface
{
    protected ?int $id = null;
    protected string $elementType;
    protected int $elementId;
    protected string $integrator;
    protected array $data = [];
    protected string $releaseType;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setElementType(string $elementType): void
    {
        $this->elementType = $elementType;
    }

    public function getElementType(): string
    {
        return $this->elementType;
    }

    public function setElementId($elementId): void
    {
        $this->elementId = $elementId;
    }

    public function getElementId(): int
    {
        return $this->elementId;
    }

    public function setIntegrator(string $integrator): void
    {
        $this->integrator = $integrator;
    }

    public function getIntegrator(): string
    {
        return $this->integrator;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getReleaseType(): string
    {
        return $this->releaseType;
    }

    public function setReleaseType(string $releaseType): void
    {
        $this->releaseType = $releaseType;
    }
}
