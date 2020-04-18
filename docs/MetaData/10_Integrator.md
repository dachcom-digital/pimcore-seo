# Integrators
Before you can use integrators, you need to add them in your product setup:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: title_description
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                -   integrator_name: html_tag
                -   integrator_name: schema
```

# Integration Rendering Types
If you want to render integrator as `fieldset` instead of `tab` (default), you need to change it like that:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            integrator_rendering_type: 'fieldset'
```

## Available Integrators

- [Title & Description Integrator](./Integrator/10_TitleDescriptionIntegrator.md)
- [Open Graph Integrator](./Integrator/11_OpenGraphIntegrator.md)
- [HTML-Tag Integrator](./Integrator/12_HtmlTagIntegrator.md)
- [Schema Integrator](./Integrator/13_SchemaIntegrator.md)
