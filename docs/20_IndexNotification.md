# Index Notification Section

## Full Configuration Example

```yaml
seo:
    index_provider_configuration:
        enabled_worker:
            -   worker_name: google_index
                worker_config:
                    auth_config: app/config/pimcore/google-api-private-key.json # default pimcore: app/config/pimcore/google-api-private-key.json
        pimcore_element_watcher:
            enabled: true
```