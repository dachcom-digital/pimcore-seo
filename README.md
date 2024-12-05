# Pimcore SEO Bundle
[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Software License](https://img.shields.io/badge/license-DCL-white.svg?style=flat-square&color=%23ff5c5c)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/seo.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/seo)
[![Tests](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-seo/.github/workflows/codeception.yml?branch=master&style=flat-square&logo=github&label=codeception)](https://github.com/dachcom-digital/pimcore-seo/actions?query=workflow%3ACodeception+branch%3Amaster)
[![PhpStan](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-seo/.github/workflows/php-stan.yml?branch=master&style=flat-square&logo=github&label=phpstan%20level%204)](https://github.com/dachcom-digital/pimcore-seo/actions?query=workflow%3A"PHP+Stan"+branch%3Amaster)

The last SEO Bundle for pimcore you'll ever need!

- Create title, description and meta tags (OG-Tags, Twitter-Cards) for documents **and** objects!
- Shipped with a save and user-friendly editor with multi locale support!
- Enjoy live previews of each social channel!
- Super smooth and simple PHP-API to update meta information of documents or objects!
- Submit content data to search engines like Google, Bing, DuckDuckGo in real time!
- Fully backwards compatible if you're going to install this bundle within an existing pimcore instance!

## Documents
![image](https://user-images.githubusercontent.com/700119/79641134-db71cd00-8195-11ea-81c4-e2bbdb7073f5.png)

## Objects
![image](https://user-images.githubusercontent.com/700119/79641347-39eb7b00-8197-11ea-9ef7-9ec41f8c2057.png)

## Objects | Tabbed View
![image](https://user-images.githubusercontent.com/700119/79804274-0578ea00-8364-11ea-8780-3cd8b2d72376.png)

### Release Plan

| Release | Supported Pimcore Versions | Supported Symfony Versions | Release Date | Maintained     | Branch |
|---------|----------------------------|----------------------------|--------------|----------------|--------|
| **3.x** | `11.0`                     | `6.2`                      | 30.08.2023   | Feature Branch | master |
| **2.x** | `10.1` - `10.6`            | `5.4`                      | 14.10.2021   | Unsupported    | 2.x    |
| **1.x** | `6.0` - `6.9`              | `3.4`, `^4.4`              | 27.04.2020   | Unsupported    | 1.x    |


## Installation

```json
"require" : {
    "dachcom-digital/seo" : "~3.2.0",
}
```

Add Bundle to `bundles.php`:
```php
return [
    SeoBundle\SeoBundle::class => ['all' => true],
];
```

- Execute: `$ bin/console pimcore:bundle:install SeoBundle`

## Upgrading
- Execute: `$ bin/console doctrine:migrations:migrate --prefix 'SeoBundle\Migrations'`

## Usage
This Bundle needs some preparation. Please check out the [Setup && Overview](docs/00_Setup.md) guide first.

## Further Information
- [Setup & Overview](docs/00_Setup.md)
- [Meta Data](./docs/10_MetaData.md) [Set Title, Description, ...]
  - [Integrators](./docs/MetaData/10_Integrator.md)
    - [Title & Description Integrator](./docs/MetaData/Integrator/10_TitleDescriptionIntegrator.md)
    - [Open Graph Integrator](./docs/MetaData/Integrator/11_OpenGraphIntegrator.md)
    - [Twitter Card Integrator](./docs/MetaData/Integrator/12_TwitterCardIntegrator.md)
    - [Schema Integrator](./docs/MetaData/Integrator/13_SchemaIntegrator.md)
    - [HTML-Tag Integrator](./docs/MetaData/Integrator/14_HtmlTagIntegrator.md)
  - [Extractors](./docs/MetaData/20_Extractors.md)
    - [Custom Extractor](./docs/MetaData/Extractor/10_CustomExtractor.md)
    - [Third Party Extractors](./docs/MetaData/Extractor/11_ThirdPartyExtractors.md)
  - [Middleware](docs/MetaData/30_Middleware.md)
- [Index Notification](docs/20_IndexNotification.md) [Push Data to Google Index]
  - [Google Worker](docs/IndexNotification/Worker/01_GoogleWorker.md) [Push Data to Google Index]

## Supported 3rd Party Bundles
- Use [dachcom-digital/jobs](https://github.com/dachcom-digital/pimcore-jobs) to push job data via google index!
- Use [dachcom-digital/schema](https://github.com/dachcom-digital/pimcore-schema) to generate schema blocks via PHP API with ease!

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## License
**DACHCOM.DIGITAL AG**, Löwenhofstrasse 15, 9424 Rheineck, Schweiz  
[dachcom.com](https://www.dachcom.com), dcdi@dachcom.ch  
Copyright © 2024 DACHCOM.DIGITAL. All rights reserved.  

For licensing details please visit [LICENSE.md](LICENSE.md)  
