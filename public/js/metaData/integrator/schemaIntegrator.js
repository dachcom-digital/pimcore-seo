pimcore.registerNS('Seo.MetaData.Integrator.SchemaIntegrator');
Seo.MetaData.Integrator.SchemaIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.schema.title'),
    iconClass: 'seo_integrator_icon_icon_schema',
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
                    html: t('seo_bundle.integrator.schema.usage_note').format('<pre style="display: inline;">"&lt;script type="application/ld+json"&gt;"</pre>', '<pre style="display: inline;">"&lt;/script&gt;"</pre>')
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
            user = pimcore.globalmanager.get('user');

        if(user.isAllowed('seo_bundle_add_property') === false) {
            return [];
        }

        items.push({
            cls: 'pimcore_block_button_plus',
            text: t('seo_bundle.integrator.schema.add_field'),
            iconCls: 'pimcore_icon_plus',
            handler: this.addSchemaField.bind(this, null)
        });

        items.push({
            xtype: 'container',
            flex: 1,
            html: t('seo_bundle.integrator.schema.caution_note'),
            style: {
                padding: '5px',
                border: '1px solid #A4E8A6',
                background: '#dde8c9',
                margin: '0 0 10px 0',
                color: 'black'
            }
        });

        return new Ext.Toolbar({
            items: items
        });
    },

    addSchemaField: function (fieldId) {

        var itemContainer,
            itemFieldContainer,
            assertedFieldId = fieldId ? fieldId : Ext.id(),
            identifierValue = this.getStoredValue(assertedFieldId, 'identifier', null),
            user = pimcore.globalmanager.get('user');

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
                    hidden: !user.isAllowed('seo_bundle_remove_property'),
                    listeners: {
                        click: this.removeSchemaField.bind(this)
                    }
                }
            ]
        });

        itemContainer = new Ext.form.Panel({
            title: false,
            autoScroll: false,
            border: false,
            items: [
                {
                    xtype: 'hidden',
                    fieldLabel: 'Identifier',
                    labelAlign: this.configuration.useLocalizedFields ? 'top' : 'left',
                    name: assertedFieldId + '_identifier',
                    value: identifierValue === null || identifierValue === undefined ? assertedFieldId : identifierValue,
                },
                itemFieldContainer
            ]
        });

        this.schemaPanel.add(itemContainer);
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

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension(fieldId, this.getAvailableLocales());

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
            fieldLabel: t('seo_bundle.integrator.schema.data'),
            style: 'margin: 0 10px 0 0',
            name: lfIdentifier,
            height: 200,
            value: this.getStoredValue(lfIdentifier, 'data', locale),
            inputAttrTpl: 'spellcheck="false"',
            flex: 1,
        }
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {
        return [{
            title: t('seo_bundle.integrator.schema.data'),
            storeIdentifier: lfIdentifier,
            renderer: function (v) {
                if (typeof v === 'string') {
                    return Ext.util.Format.stripTags(v);
                }
                return (v === '' || v === null) ? '--' : v;
            },
            onFetchStoredValue: function (locale) {
                return this.getStoredValue(lfIdentifier, 'data', locale);
            }.bind(this)
        }];
    },

    removeSchemaField: function (btn) {
        this.schemaPanel.remove(btn.up('panel'));
    },

    getStoredValue: function (fieldId, node, locale) {

        var value;

        value = this.getStoredValueOfType(fieldId, node, locale, 'form');

        if (value !== null) {
            return value;
        }

        return this.getStoredValueOfType(fieldId, node, locale, 'storage');
    },

    getStoredValueOfType: function (fieldId, node, locale, type) {

        var value, formValues,
            localizedValue = null,
            values = {};

        if (type === 'form') {
            formValues = this.formPanel.getForm().getValues();
            if (Ext.isObject(formValues)) {
                Ext.Object.each(formValues, function (fieldId, data) {
                    if (fieldId.indexOf('_identifier') === -1) {
                        values[fieldId] = {localized: Ext.isArray(data), data: data, identifier: values[fieldId + '_identifier']}
                    }
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
            return value[node];
        }

        if (!Ext.isArray(value[node])) {
            return value[node];
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
            if (fieldId.indexOf('_identifier') === -1) {
                returnValues.push({localized: Ext.isArray(data), data: data, identifier: values[fieldId + '_identifier']});
            }
        });

        return returnValues;
    },

    getValuesForPreview: function () {
        return [];
    }
});