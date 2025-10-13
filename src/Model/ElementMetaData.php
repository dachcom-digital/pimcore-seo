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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'seo_element_meta_data')]
#[UniqueConstraint(name: 'element_type_id_integrator', columns: ['element_type', 'element_id', 'integrator', 'release_type'])]
class ElementMetaData implements ElementMetaDataInterface
{
    #[Id]
    #[Column(name: 'id', type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Column(name: 'element_type', type: 'string', nullable: false)]
    protected string $elementType;
    #[Column(name: 'element_id', type: 'integer', nullable: false)]
    protected int $elementId;
    #[Column(name: 'integrator', type: 'string', nullable: false)]
    protected string $integrator;
    #[Column(name: 'data', type: 'array', nullable: false)]
    protected array $data = [];
    #[Column(name: 'release_type', type: 'string', nullable: false, options: ['default' => 'public'])]
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
