pimcore.registerNS('Seo.MetaData.Integrator.SchemaIntegrator');
Seo.MetaData.Integrator.SchemaIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('Schema Blocks'),
    schemaPanel: null,

    isCollapsed: function () {
        return !this.hasData();
    },

    buildPanel: function () {

        this.schemaPanel = new Ext.form.Panel({
            title: false,
            autoScroll: false,
            border: false,
            items: [
                {
                    xtype: 'label',
                    anchor: '100%',
                    style: 'display:block; padding:5px; background:#f5f5f5; border:1px solid #eee; font-weight: 300;',
                    html: 'HTML tags are not allowed and are removed when saving. Valid data starts with <pre style="display: inline;">"&lt;script type="application/ld+json"&gt;"</pre> and ends with <pre style="display: inline;">"&lt;/script&gt;"</pre>.'
                },
                this.getAddControl()
            ]
        });

        this.setupStoredData();

        return [this.schemaPanel];
    },

    setupStoredData: function () {

        var idAwareData = {};

        if (!Ext.isArray(this.data)) {
            return;
        }

        // transformData first: assign unique id to each schema element
        // we don't save any extJs ids in db but here we need some to allocate the right schema element.
        Ext.Array.each(this.data, function (ogFieldData) {
            var fieldId = Ext.id();
            idAwareData[fieldId] = ogFieldData;
        }.bind(this));

        this.data = idAwareData;

        Ext.Object.each(this.data, function (fieldId) {
            this.addSchemaField(fieldId);
        }.bind(this));
    },

    getAddControl: function () {

        var items = [],
            configuration = this.getConfiguration(),
            hasDynamicallyAddedJsonLdData = false,
            usedJsonLdData = [];

        if (configuration.hasOwnProperty('hasDynamicallyAddedJsonLdData')) {
            hasDynamicallyAddedJsonLdData = configuration.hasDynamicallyAddedJsonLdData;
        }

        items.push({
            cls: 'pimcore_block_button_plus',
            text: 'Add Schema Field',
            iconCls: 'pimcore_icon_plus',
            handler: this.addSchemaField.bind(this, null)
        });

        if (hasDynamicallyAddedJsonLdData === true) {

            Ext.Object.each(configuration.dynamicallyAddedJsonLdDataTypes, function (jsonLdType, jsonLdTypeCount) {
                usedJsonLdData.push('"' + jsonLdType + '" (' + jsonLdTypeCount + 'x)');
            });

            items.push({
                xtype: 'label',
                text: t(' This element has dynamic JSON-LD extractor attached: ' + usedJsonLdData.join(', ') + '. You may face duplicate content if the same schema block gets added multiple times!'),
                style: {
                    padding: '5px',
                    border: '1px solid #A4E8A6',
                    display: 'inline-block',
                    background: '#dde8c9',
                    margin: '0 0 10px 0',
                    color: 'black'
                }
            })
        }

        return new Ext.Toolbar({
            items: items
        });
    },

    addSchemaField: function (fieldId) {

        var itemFieldContainer,
            assertedFieldId = fieldId ? fieldId : Ext.id();

        itemFieldContainer = new Ext.form.FieldContainer({
            xtype: 'fieldcontainer',
            width: 700,
            layout: 'hbox',
            style: {
                marginTop: '5px',
                paddingBottom: '5px',
                borderBottom: '1px dashed #b1b1b1;'
            },
            items: [
                this.getSchemaField(assertedFieldId),
                {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_delete',
                    width: 50,
                    listeners: {
                        click: this.removeSchemaField.bind(this)
                    }
                }
            ]
        });

        this.schemaPanel.add(itemFieldContainer);
    },

    getSchemaField: function (fieldId) {

        var lfExtension, params;

        if (this.configuration.useLocalizedFields === false) {
            return this.getSchemaEditorField(false, fieldId, null);
        }

        params = {
            showFieldLabel: false,
            onGridRefreshRequest: this.refreshLivePreviewDelayed.bind(this),
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.getSchemaEditorField.bind(this, true)
        };

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension(fieldId);

        return {
            xtype: 'fieldcontainer',
            label: false,
            style: 'margin: 0 10px 0 0',
            flex: 3,
            autoWidth: true,
            items: [
                lfExtension.generateLocalizedField(params)
            ]
        };
    },

    getSchemaEditorField: function (isProxy, lfIdentifier, locale) {
        return {
            xtype: 'textarea',
            fieldLabel: t('Schema Data'),
            style: 'margin: 0 10px 0 0',
            name: lfIdentifier,
            height: 200,
            value: this.getStoredValue(lfIdentifier, locale),
            inputAttrTpl: 'spellcheck="false"',
            flex: 1,
        }
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {
        return [{
            title: t('Schema Data'),
            storeIdentifier: lfIdentifier,
            renderer: function (v) {
                if (typeof v === 'string') {
                    return Ext.util.Format.stripTags(v);
                }
                return (v === '' || v === null) ? '--' : v;
            },
            onFetchStoredValue: function (locale) {
                return this.getStoredValue(lfIdentifier, locale);
            }.bind(this)
        }];
    },

    removeSchemaField: function (btn) {
        this.schemaPanel.remove(btn.up('fieldcontainer'));
    },

    getStoredValue: function (fieldId, locale) {

        var value;

        value = this.getStoredValueOfType(fieldId, locale, 'form');

        if (value !== null) {
            return value;
        }

        return this.getStoredValueOfType(fieldId, locale, 'storage');
    },

    getStoredValueOfType: function (fieldId, locale, type) {

        var value, formValues,
            localizedValue = null,
            values = {};

        if (type === 'form') {
            formValues = this.formPanel.getForm().getValues();
            if (!Ext.isObject(formValues)) {
                Ext.Object.each(formValues, function (fieldId, data) {
                    values[fieldId] = {localized: Ext.isArray(data), data: data}
                });
            }
        } else if (type === 'storage') {
            values = this.data;
        }

        if (!Ext.isObject(values)) {
            return null;
        }

        if (!values.hasOwnProperty(fieldId)) {
            return null;
        }

        value = values[fieldId];
        if (!Ext.isObject(value)) {
            return null;
        }

        if (value.localized === false) {
            return value.data;
        }

        Ext.Array.each(value.data, function (localizedValueData) {
            if (localizedValueData.locale === locale) {
                localizedValue = localizedValueData.value;
                return false;
            }
        });

        return localizedValue;
    },

    getValues: function () {

        var returnValues = [],
            values = this.formPanel.getForm().getValues();

        if (!Ext.isObject(values)) {
            return [];
        }

        Ext.Object.each(values, function (fieldId, data) {
            returnValues.push({localized: Ext.isArray(data), data: data})
        });

        return returnValues;
    },

    getValuesForPreview: function () {
        return [];
    }
});