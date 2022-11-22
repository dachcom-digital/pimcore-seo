pimcore.registerNS('Seo.MetaData.Integrator.HtmlTagIntegrator');
Seo.MetaData.Integrator.HtmlTagIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.html.title') + ' (&lt;meta .../&gt; &lt;link .../&gt; ...)',
    addPresetButtonLabel: t('seo_bundle.integrator.property.add_preset'),
    iconClass: 'seo_integrator_icon_html_tags',
    htmlTagPanel: null,

    isCollapsed: function () {
        return !this.hasData();
    },

    buildPanel: function () {

        this.htmlTagPanel = new Ext.Panel({
            title: false,
            autoScroll: false,
            border: false,
            items: [this.getAddControl()]
        });

        this.setupStoredData();

        return [this.htmlTagPanel];
    },

    setupStoredData: function () {

        if (!Ext.isArray(this.data)) {
            return;
        }

        Ext.Array.each(this.data, function (htmlTagValue) {
            var presetData = this.getPresetByValue(htmlTagValue);
            this.addHtmlTagField(
                htmlTagValue,
                presetData !== null,
                presetData !== null && presetData.hasOwnProperty('label') ? presetData.label : null
            );
        }.bind(this));
    },

    getAddControl: function () {

        var items = [],
            presetMenu = [],
            configuration = this.getConfiguration(),
            availablePresets = configuration.hasOwnProperty('presets') ? configuration.presets : [],
            presetsOnlyMode = configuration.hasOwnProperty('presets_only_mode') ? configuration.presets_only_mode : false,
            user = pimcore.globalmanager.get('user');

        if (user.isAllowed('seo_bundle_add_property') === false) {
            return [];
        }

        if (presetsOnlyMode === false) {
            items.push({
                cls: 'pimcore_block_button_plus',
                text: t('seo_bundle.integrator.html.add_field'),
                iconCls: 'pimcore_icon_plus',
                handler: this.addHtmlTagField.bind(this, null, null, null)
            });
        }

        if (availablePresets.length > 0) {

            Ext.Array.each(availablePresets, function (preset) {

                var label = preset.hasOwnProperty('label') ? preset.label : ('Preset ' + index),
                    icon = preset.hasOwnProperty('icon_class') && preset.icon_class !== null ? preset.icon_class : 'pimcore_icon_brick';

                presetMenu.push({
                    text: label,
                    iconCls: icon,
                    handler: this.addHtmlTagField.bind(this, preset.hasOwnProperty('value') ? preset.value : '#', true, label)
                });

            }.bind(this));

            items.push({
                cls: 'pimcore_block_button_plus',
                text: this.addPresetButtonLabel,
                iconCls: 'pimcore_icon_objectbricks',
                menu: presetMenu
            });
        }

        if (presetsOnlyMode === false) {
            items.push({
                xtype: 'container',
                flex: 1,
                html: t('seo_bundle.integrator.html.caution_note'),
                style: {
                    padding: '5px',
                    border: '1px solid #b32d2d',
                    background: '#e8acac',
                    margin: '0 0 10px 0',
                    color: 'black'
                }
            });
        }

        return new Ext.Toolbar({
            items: items
        });
    },

    getPresetByValue: function (htmlTagValue) {

        var presetData = null,
            configuration = this.getConfiguration(),
            availablePresets = configuration.hasOwnProperty('presets') ? configuration.presets : [];

        if (availablePresets.length === 0) {
            return null;
        }

        Ext.Array.each(availablePresets, function (preset) {
            if (preset.hasOwnProperty('value') && preset.value === htmlTagValue) {
                presetData = preset;
                return false;
            }

        }.bind(this));

        return presetData;
    },

    addHtmlTagField: function (fieldValue, disabled, tagName) {

        var itemFieldContainer,
            user = pimcore.globalmanager.get('user');

        itemFieldContainer = new Ext.form.FieldContainer({
            xtype: 'fieldcontainer',
            width: 800,
            layout: 'hbox',
            style: {
                marginTop: '5px',
                paddingBottom: '5px',
                borderBottom: '1px dashed #b1b1b1;'
            },
            items: [
                {
                    xtype: 'textfield',
                    labelWidth: 170,
                    fieldLabel: Ext.isString(tagName) ? tagName : t('seo_bundle.integrator.html.tag'),
                    style: 'margin: 0 10px 0 0',
                    name: 'tags',
                    value: fieldValue,
                    readOnly: disabled === true,
                    flex: 1,
                },
                {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_delete',
                    width: 50,
                    hidden: !user.isAllowed('seo_bundle_remove_property'),
                    listeners: {
                        click: this.removeHtmlTagField.bind(this)
                    }
                }
            ]
        });

        this.htmlTagPanel.add(itemFieldContainer);
    },

    removeHtmlTagField: function (btn) {

        var panel = btn.up('fieldcontainer');

        this.htmlTagPanel.remove(panel);
    },

    getValues: function () {

        var values = this.formPanel.getForm().getValues();

        if (!values.hasOwnProperty('tags')) {
            return [];
        }

        if (Ext.isString(values.tags)) {
            return [values.tags];
        }

        if (!Ext.isArray(values.tags)) {
            return [];
        }

        return values.tags;
    },

    getValuesForPreview: function () {
        return [];
    }
});
