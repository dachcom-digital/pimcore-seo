# HTML Tag Integrator
This integrator is not recommend but required, if you're going to integrate this bundle within a existing pimcore installation.
It allows you to add raw html tags to your document head.

## Configuration

# Default Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: html_tag
```
