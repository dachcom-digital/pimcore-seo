# Upgrade Notes

## 2.2.1
- [BUGFIX] Skip meta data update when elementId is missing [@NiklasBr](https://github.com/dachcom-digital/pimcore-seo/pull/58)

## 2.2.0
- [BUGFIX] fix encoding in schema json validation process [#45](https://github.com/dachcom-digital/pimcore-seo/issues/45)
- [IMPROVEMENT] Respect Pimcore fallback languages [#44](https://github.com/dachcom-digital/pimcore-seo/issues/44)

## 2.1.0
- [BUGFIX] Fix Array Merge Process [#32](https://github.com/dachcom-digital/pimcore-seo/issues/32)
- [BUGFIX] Avoid triggering deprecation message on isMasterRequest() [#36](https://github.com/dachcom-digital/pimcore-seo/pull/36)
- [BUGFIX] Support CoreShop 3 Thumbnails [@dkarlovi](https://github.com/dachcom-digital/pimcore-seo/pull/30)
- [IMPROVEMENT] Avoid duplicate integrator registration [#28](https://github.com/dachcom-digital/pimcore-seo/issues/28)
- [IMPROVEMENT] Allow preview images with spaces [#35](https://github.com/dachcom-digital/pimcore-seo/issues/35)
- [NEW FEATURE]  Add presets to html tag integrator [#37](https://github.com/dachcom-digital/pimcore-seo/issues/37)

## Migrating from Version 1.x to Version 2.0.0

### Global Changes
- PHP8 return type declarations added: you may have to adjust your extensions accordingly

***

SeoBundle 1.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-seo/blob/1.x/UPGRADE.md
