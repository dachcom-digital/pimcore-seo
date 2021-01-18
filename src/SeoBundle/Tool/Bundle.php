<?php

namespace SeoBundle\Tool;

class Bundle
{
    /**
     * @param string $bundleName
     * @param array  $installedBundle
     *
     * @return bool
     */
    public static function hasBundle(string $bundleName, array $installedBundle)
    {
        return array_key_exists($bundleName, $installedBundle);
    }

    /**
     * @param string $bundleName
     * @param array  $installedBundle
     *
     * @return bool
     */
    public static function hasDachcomBundle(string $bundleName, array $installedBundle)
    {
        if (!array_key_exists($bundleName, $installedBundle)) {
            return false;
        }

        $class = $installedBundle[$bundleName];
        if (!defined(sprintf('%s::PACKAGE_NAME', $class))) {
            return false;
        }

        if (strpos($class::PACKAGE_NAME, 'dachcom-digital') === false) {
            return false;
        }

        return true;
    }
}
