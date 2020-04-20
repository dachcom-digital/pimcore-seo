# Pimcore SEO Bundle
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

#### Requirements
* Pimcore >= 6.0.0

## Installation

```json
"require" : {
    "dachcom-digital/seo" : "~1.0.0",
}
```

### Installation via Extension Manager
After you have installed the SEO Bundle via composer, open pimcore backend and go to `Tools` => `Extension`:
- Click the green `+` Button in `Enable / Disable` row
- Click the green `+` Button in `Install/Uninstall` row

### Installation via CommandLine
After you have installed the SEO Bundle via composer:
- Execute: `$ bin/console pimcore:bundle:enable SeoBundle`
- Execute: `$ bin/console pimcore:bundle:install SeoBundle`

## Upgrading

### Upgrading via Extension Manager
After you have updated the SEO Bundle via composer, open pimcore backend and go to `Tools` => `Extension`:
- Click the green `+` Button in `Update` row

### Upgrading via CommandLine
After you have updated the SEO Bundle via composer:
- Execute: `$ bin/console pimcore:bundle:update SeoBundle`

### Migrate via CommandLine
Does actually the same as the update command and preferred in CI-Workflow:
- Execute: `$ bin/console pimcore:migrations:migrate -b SeoBundle`

## Usage
This Bundle needs some preparation. Please checkout the [Setup && Overview](docs/00_Setup.md) guide first.

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
  - [Middleware](docs/MetaData/30_Middleware.md)
- [Index Notification](docs/20_IndexNotification.md) [Push Data to Google Index]
  - [Google Worker](docs/IndexNotification/Worker/01_GoogleWorker.md) [Push Data to Google Index]

## Supported 3rd Party Bundles
Use [dachcom-digital/jobs](https://github.com/dachcom-digital/pimcore-jobs) to push job data via google index!
Use [dachcom-digital/schema](https://github.com/dachcom-digital/pimcore-schema) to generate schema blocks via PHP API with ease!

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
