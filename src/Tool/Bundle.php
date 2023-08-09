<?php

namespace SeoBundle\Tool;

class Bundle
{
    public static function hasBundle(string $bundleName, array $installedBundle): bool
    {
        return array_key_exists($bundleName, $installedBundle);
    }

    public static function hasDachcomBundle(string $bundleName, array $installedBundle): bool
    {
        if (!array_key_exists($bundleName, $installedBundle)) {
            return false;
        }

        $class = $installedBundle[$bundleName];
        if (!defined(sprintf('%s::PACKAGE_NAME', $class))) {
            return false;
        }

        if (!str_contains($class::PACKAGE_NAME, 'dachcom-digital')) {
            return false;
        }

        return true;
    }
}
