# Twitter Card Integrator
This Integrator allows you to define meta tags for twitter cards. It also renders a live preview.

## Configuration

# Default Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: twitter_card
                    integrator_config:
                        twitter_image_thumbnail: 'socialThumb'
```

## Extended Configuration
Add some more `types`, `properties` and with this configuration:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: open_graph
                    integrator_config:
                        twitter_image_thumbnail: 'socialThumb'
                        types:
                            - ['my_type', 'my_type']
                        properties:
                            - ['twitter:definition', 'twitter:definition', true] # 3. argument: allow export to xliff translation
```
