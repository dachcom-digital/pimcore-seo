# Open Graph Integrator
This Integrator allows you to define Open Graph meta tags.
It also renders a live preview (Facebook).

## Notes
You may have noticed, that there is no `og:url` in your property selection.
This property gets appended automatically. Make sure your [link generators](https://pimcore.com/docs/5.x/Development_Documentation/Objects/Object_Classes/Class_Settings/Link_Generator.html) for objects are up and running! 

## Configuration

# Default Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
```

## Extended Configuration
Add some more `types`, `properties` and `presetts` with this configuration:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                        types:
                            - ['my_type', 'my_type']
                        properties:
                            - ['og:test', 'og:test']
                        presets:
                            -   label: 'My Preset'
                                icon_class: 'pimcore_icon_user'
                                fields:
                                    -   property: 'og:type'
                                        content: 'og:article'
                                    -   property: 'og:description'
                                        content: null
                                    -   property: 'og:title'
                                        content: null
```
