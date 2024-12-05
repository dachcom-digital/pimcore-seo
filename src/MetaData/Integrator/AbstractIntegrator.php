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

use Pimcore\Tool;

abstract class AbstractIntegrator
{
    protected function findLocaleAwareData(
        mixed $data,
        ?string $requestedLocale,
        string $returnType = 'scalar'
    ): mixed {
        if ($requestedLocale === null) {
            return null;
        }

        $value = $this->findData($data, $requestedLocale, $returnType);

        if ($value !== null) {
            return $value;
        }

        foreach (Tool::getFallbackLanguagesFor($requestedLocale) as $fallBackLocale) {
            if (null !== $fallBackValue = $this->findData($data, $fallBackLocale, $returnType)) {
                return $fallBackValue;
            }
        }

        return null;
    }

    protected function findData(mixed $data, string $locale, string $returnType = 'scalar'): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if (count($data) === 0) {
            return null;
        }

        $index = array_search($locale, array_column($data, 'locale'), true);
        if ($index === false) {
            return null;
        }

        $value = $data[$index]['value'];

        if (empty($value)) {
            return null;
        }

        if ($returnType === 'scalar' && !is_scalar($value)) {
            return null;
        }

        if ($returnType === 'array' && !is_array($value)) {
            return null;
        }

        return $value;
    }
}
