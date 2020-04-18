# Setup
After you have enabled this Bundle, there are some global steps to define.

## Frontend
First, setup your twig layout. 

### Default Layout
Your default layout metadata head should look like this. If you've enabled the `auto_detect_documents` [feature](./MetaData/10_Integrator.md), 
all documents will be automatically rendered with corresponding metadata values.

```twig
{# layout.html.twig #}

<!DOCTYPE html>
<html>
<head>
    {% block metadata %}
        {{ pimcore_head_title() }}
        {{ pimcore_head_meta() }}
    {% endblock metadata %}
</head>
<body>
{% block content %}
    <p>Some page body.</p>
{% endblock content %}
</body>
</html>
```

### Sub-Layout
On a special route - mostly a static route - you need to inform the meta provider to extract metadata from your object.

```twig
{# product/detail.html.twig #}

{% extends 'layout.html.twig' %}

{% block metadata %}
    {% do seo_update_metadata(object, app.request.locale) %}
    {{ parent() }}
{% endblock metadata %}

{% block content %}
    {# my object layout #}
{% endblock content %}
```

## Deprecate Pimcore SEO Features
With this bundle, you may don't want to use the default SEO panel in pimcore documents. 
To disable the panel in backend you need to set `hide_pimcore_default_seo_panel` to `true`.

> **Note**: Already populated fields like title, description and all meta fields will automatically moved to the seo bundle context. No migration needed! 

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            documents:
                enabled: true
                hide_pimcore_default_seo_panel: true
```

## Next Steps
Learn more about metadata or the index notification.

- [Meta Data](./docs/10_MetaData.md) [Set Title, Description, ...]
- [Index Notification](docs/20_IndexNotification.md) [Push Data to Google Index]


## Configuration
A full configuration layout could look like this:

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
                    - MyObject
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