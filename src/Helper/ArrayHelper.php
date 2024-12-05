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

namespace SeoBundle\Helper;

class ArrayHelper
{
    public function mergeNonLocaleAwareArrays(
        array $data,
        ?array $previousData,
        string $rowIdentifier = 'name',
        bool $mergeWithPrevious = false
    ): ?array {
        if ($mergeWithPrevious === false) {
            return $data;
        }

        $newData = $previousData;

        foreach ($data as $row) {
            $previousRowIndex = array_search($row[$rowIdentifier], array_column($previousData, $rowIdentifier), true);

            if ($previousRowIndex === false) {
                $newData[] = $row;

                continue;
            }

            $newData[$previousRowIndex] = $row;
        }

        return $newData;
    }

    public function mergeLocaleAwareArrays(
        array $data,
        ?array $previousData,
        string $rowIdentifier = 'name',
        string $dataIdentifier = 'value',
        bool $mergeWithPrevious = false
    ): ?array {
        $cleanedRows = $this->cleanEmptyLocaleRows($data, $dataIdentifier);

        // nothing to merge
        if (!is_array($previousData) || count($previousData) === 0) {
            return $cleanedRows;
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        $newDataIndex = 0;

        if ($cleanedRows === null) {
            return null;
        }

        foreach ($cleanedRows as $row) {
            $previousRowIndex = array_search($row[$rowIdentifier], array_column($previousData, $rowIdentifier), true);

            if ($previousRowIndex === false) {
                $newData[$newDataIndex] = $row;
                $newDataIndex++;

                continue;
            }

            $dataIndex = $mergeWithPrevious ? $previousRowIndex : $newDataIndex;

            $rebuildRow = $previousData[$previousRowIndex][$dataIdentifier];
            $currentValue = $row[$dataIdentifier];

            // it's not a localized field value
            if (!is_array($currentValue) || $this->isAssocArray($currentValue)) {
                $newData[$dataIndex] = $row;
                $newDataIndex++;

                continue;
            }

            $row[$dataIdentifier] = $this->rebuildLocaleValueRow($currentValue, $rebuildRow, $mergeWithPrevious);

            if (count($row[$dataIdentifier]) > 0) {
                $newData[$dataIndex] = $row;
            }

            $newDataIndex++;
        }

        return $newData;
    }

    public function rebuildLocaleValueRow(array $values, array $rebuildRow, bool $mergeWithPrevious = false): array
    {
        // clean-up rebuild row
        $allowedLocales = array_map(static function (array $row) {
            return $row['locale'];
        }, $values);

        $cleanedRebuildRow = [];
        foreach ($rebuildRow as $rebuildLine) {
            $locale = $rebuildLine['locale'];

            if ($mergeWithPrevious === false && !in_array($locale, $allowedLocales, true)) {
                continue;
            }

            if (!array_key_exists($locale, $cleanedRebuildRow)) {
                $cleanedRebuildRow[$locale] = $rebuildLine;
            }
        }

        $cleanedRebuildRow = array_values($cleanedRebuildRow);

        foreach ($values as $currentRow) {
            $locale = $currentRow['locale'];
            $value = $currentRow['value'];

            $index = array_search($locale, array_column($cleanedRebuildRow, 'locale'), true);

            if ($index !== false) {
                if ($value === null) {
                    unset($cleanedRebuildRow[$index]);
                } else {
                    $cleanedRebuildRow[$index] = $currentRow;
                }
            } elseif ($value !== null) {
                $cleanedRebuildRow[] = $currentRow;
            }
        }

        return array_values($cleanedRebuildRow);
    }

    public function cleanEmptyLocaleRows(array $field, string $dataIdentifier = 'value'): ?array
    {
        $cleanData = [];
        foreach ($field as $row) {
            if ($row[$dataIdentifier] === null) {
                continue;
            }

            if (is_array($row[$dataIdentifier]) && $this->isAssocArray($row[$dataIdentifier]) === false) {
                if (null !== $cleanRowValues = $this->cleanEmptyLocaleValues($row[$dataIdentifier])) {
                    $cleanData[] = array_replace($row, [$dataIdentifier => $cleanRowValues]);
                }

                continue;
            }

            // it's not a localized field, keep it as it is
            $cleanData[] = $row;
        }

        return count($cleanData) === 0 ? null : $cleanData;
    }

    public function cleanEmptyLocaleValues(array $field): ?array
    {
        $cleanData = [];
        foreach ($field as $row) {
            if ($row['value'] !== null) {
                $cleanData[] = $row;
            }
        }

        return count($cleanData) === 0 ? null : $cleanData;
    }

    public function isAssocArray(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return !array_is_list($array);
    }
}
