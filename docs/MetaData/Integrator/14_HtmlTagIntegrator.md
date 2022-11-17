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

## Extended Configuration

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: html_tag
                    integrator_config:
                        presets_only_mode: false
                        presets:
                            -
                                label: 'Robots No-Index'
                                value: '<meta name="robots" content="noindex, nofollow">'
                                icon_class: 'pimcore_icon_stop'
                            -   label: 'Secret Meta-Link'
                                value: '<meta name="secret" content="secret-thing">'
```

### presets
If defined, the user is able to add predefined presets. They can only be added and not edited!

![image](https://user-images.githubusercontent.com/700119/202385523-b748a0d3-56c3-4403-980f-53c60c2b45e8.png)

![image](https://user-images.githubusercontent.com/700119/202278570-51d1ed18-e323-4704-b0d7-09649fca2281.png)

### presets_only_mode
If set to `true`, the user is not able to add custom tags but only presets:
![image](https://user-images.githubusercontent.com/700119/202385300-61f5d884-8d4f-4353-8c0f-5cc6c79a7b7c.png)