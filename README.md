# Pimcore SEO Bundle
This bundle is currently under heavy development and not ready for production!

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
            enabled_integrator:
                - title_description
            documents:
                enabled: true
                hide_pimcore_default_seo_panel: true
            objects:
                enabled: true
                data_classes:
                    - MyDataClass

    index_provider_configuration:
        enabled_worker:
            -   worker_name: google_index
                worker_config:
                    auth_config: app/config/pimcore/google-api-private-key.json # default pimcore: app/config/pimcore/google-api-private-key.json
        pimcore_element_watcher:
            enabled: true

```

## Dependencies
Use dachcom-digital/jobs to push job data via google index!

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
