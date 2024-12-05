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

interface XliffAwareIntegratorInterface
{
    public function validateBeforeXliffExport(string $elementType, int $elementId, array $data, string $locale): array;

    public function validateBeforeXliffImport(string $elementType, int $elementId, array $data, string $locale): ?array;
}
