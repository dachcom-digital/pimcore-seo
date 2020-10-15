<?php

namespace SeoBundle\Helper;

class ArrayHelper
{
    /**
     * @param array  $data
     * @param array  $previousData
     * @param string $rowIdentifier
     *
     * @param string $dataIdentifier
     *
     * @return array
     */
    public function mergeLocaleAwareArrays(array $data, ?array $previousData, string $rowIdentifier = 'name', $dataIdentifier = 'value')
    {
        // nothing to merge
        if (!is_array($previousData) || count($previousData) === 0) {
            return $this->cleanEmptyLocaleRows($data, $dataIdentifier);
        }

        $newData = [];
        foreach ($data as $row) {

            $previousRowIndex = array_search($row[$rowIdentifier], array_column($previousData, $rowIdentifier));

            if ($previousRowIndex === false) {
                if (null !== $cleanRowValues = $this->cleanEmptyLocaleValues($row[$dataIdentifier])) {
                    $newData[] = array_replace($row, [$dataIdentifier => $cleanRowValues]);;
                }
                continue;
            }

            $rebuildRow = $previousData[$previousRowIndex][$dataIdentifier];
            $currentValue = $row[$dataIdentifier];

            // it's not a localized field value
            if (!is_array($currentValue) || $this->isAssocArray($currentValue)) {
                $newData[] = $row;
                continue;
            }

            $row[$dataIdentifier] = $this->rebuildLocaleValueRow($currentValue, $rebuildRow);

            if (count($row[$dataIdentifier]) > 0) {
                $newData[] = $row;
            }
        }

        return $newData;
    }

    /**
     * @param array $values
     * @param array $rebuildRow
     *
     * @return array
     */
    public function rebuildLocaleValueRow(array $values, array $rebuildRow)
    {
        foreach ($values as $currentRow) {

            $locale = $currentRow['locale'];
            $value = $currentRow['value'];

            $index = array_search($locale, array_column($rebuildRow, 'locale'));

            if ($index !== false) {

                if ($value === null) {
                    unset($rebuildRow[$index]);
                } else {
                    $rebuildRow[$index] = $currentRow;
                }

                continue;

            } elseif ($value !== null) {
                $rebuildRow[] = $currentRow;
            }

        }

        return array_values($rebuildRow);
    }

    /**
     * @param array  $field
     * @param string $dataIdentifier
     *
     * @return array|null
     */
    public function cleanEmptyLocaleRows(array $field, $dataIdentifier = 'value')
    {
        if (!is_array($field)) {
            return $field;
        }

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

    /**
     * @param array $field
     *
     * @return array|null
     */
    public function cleanEmptyLocaleValues(array $field)
    {
        if (!is_array($field)) {
            return $field;
        }

        $cleanData = [];
        foreach ($field as $row) {
            if ($row['value'] !== null) {
                $cleanData[] = $row;
            }
        }

        return count($cleanData) === 0 ? null : $cleanData;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public function isAssocArray(array $array)
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
