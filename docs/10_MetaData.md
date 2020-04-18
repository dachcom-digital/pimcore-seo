# Metadata Section
There are two important things within the metadata section: integrators and extractors.

## Integrators
Integrator allows you to define metadata in backend context. There are several pre-configured integrators, but it's also possible to add your own.
It's possible to enable a dedicated SEO panel on documents and/or objects.

Enable the SEO panel on documents/objects:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            documents:
                enabled: true
            objects:
                enabled: true
                data_classes:
                    - MyObjectClass
```

This will add an additional `SEO` tab on your documents and objects of type `MyObjectClass`.

If you want to render integrator as `fieldset` instead of `tab` (default), you need to change it like that:

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            integrator_rendering_type: 'fieldset'
```

Read more about integrators and how to use them [here](./MetaData/10_Integrator.md).

## Extractors
Every extractor will extract structured data from a given object/document.
Read more about integrators and how to use them [here](./MetaData/20_Extractors.md).
 
## More Information
- [Integrators](./MetaData/10_Integrator.md)
  - [Title & Description Integrator](./MetaData/Integrator/10_TitleDescriptionIntegrator.md)
  - [Open Graph Integrator](./MetaData/Integrator/11_OpenGraphIntegrator.md)
  - [HTML-Tag Integrator](./etaData/Integrator/12_HtmlTagIntegrator.md)
  - [Schema Integrator](./MetaData/Integrator/13_SchemaIntegrator.md)
- [Extractors](./MetaData/20_Extractors.md)
  - [Custom Extractor](./MetaData/Extractor/10_CustomExtractor/.md)
  
  
## Full Configuration Example

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
                    - MyObjectClass
            enabled_integrator:
                -   integrator_name: title_description
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                -   integrator_name: html_tag
```