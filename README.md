# Pimcore SEO Bundle
The last SEO Bundle for Pimcore you'll ever need!

![image](https://user-images.githubusercontent.com/700119/79641134-db71cd00-8195-11ea-81c4-e2bbdb7073f5.png)

- Create Title, Description and Meta Tags (OG-Tags, Twitter-Cards) for Documents **and** Objects!
- Enjoy live previews of each social channel!
- Super smooth and simple PHP-API to update meta information of documents or objects!
- Submit Content-Data to search engines like Google, Bing, DuckDuckGo in real time!
- Fully backwards compatible if you're going to install this bundle within an existing pimcore instance!

#### Requirements
* Pimcore >= 6.0.0

## Installation

```json
"require" : {
    "dachcom-digital/seo" : "~1.0.0",
}
```

## Include Routes

```yaml
# app/config/routing.yml
seo:
    resource: '@SeoBundle/Resources/config/pimcore/routing.yml'
```

## Configuration

```yaml
seo:
    meta_data_configuration:
        meta_data_provider:
            auto_detect_documents: true
        meta_data_integrator:
            documents:
                enabled: true
                hide_pimcore_default_seo_panel: true
            objects:
                enabled: true
                data_classes:
                    - Job
            enabled_integrator:
                -   integrator_name: title_description
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                -   integrator_name: html_tag
    index_provider_configuration:
        enabled_worker:
            -   worker_name: google_index
                worker_config:
                    auth_config: app/config/pimcore/google-api-private-key.json # default pimcore: app/config/pimcore/google-api-private-key.json
        pimcore_element_watcher:
            enabled: true
```

## Supported 3rd Party Bundles
Use [dachcom-digital/jobs](https://github.com/dachcom-digital/pimcore-jobs) to push job data via google index!

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
