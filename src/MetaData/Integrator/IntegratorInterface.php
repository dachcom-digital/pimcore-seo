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

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface IntegratorInterface
{
    public function setConfiguration(array $configuration): void;

    public static function configureOptions(OptionsResolver $resolver): void;

    public function getBackendConfiguration(mixed $element): array;

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array;

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array;

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array;

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void;
}
