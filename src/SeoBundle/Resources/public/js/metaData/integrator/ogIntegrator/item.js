pimcore.registerNS('Seo.MetaData.Integrator.OpenGraphIntegratorItem');
Seo.MetaData.Integrator.OpenGraphIntegratorItem = Class.create({

    id: null,
    data: null,
    configuration: null,
    removeFieldCallback: null,
    refreshFieldCallback: null,

    form: null,
    integratorValueFetcher: null,

    initialize: function (id, data, removeFieldCallback, refreshFieldCallback, configuration) {
        this.id = id;
        this.data = data;
        this.removeFieldCallback = removeFieldCallback;
        this.refreshFieldCallback = refreshFieldCallback;
        this.configuration = configuration;
        this.integratorValueFetcher = new Seo.MetaData.Extension.IntegratorValueFetcher();
    },

    createItem: function () {

        this.form = new Ext.form.Panel({
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            border: false,
            style: {
                padding: '5px',
            }
        });

        this.form.add(this.getOgFieldContainer());

        return this.form;
    },

    getOgFieldContainer: function () {

        var propertyTypeStore,
            configuration = this.configuration,
            propertyTypeStoreValue = this.getStoredValue('property', null),
            propertyTypeValue = propertyTypeStoreValue === null ? 'og:type' : propertyTypeStoreValue,
            field = this.getOgContentFieldBasedOnType(propertyTypeValue, this.id);

        propertyTypeStore = new Ext.data.ArrayStore({
            fields: ['label', 'key'],
            data: configuration.hasOwnProperty('ogProperties') ? configuration.ogProperties : []
        });

        return {
            xtype: 'fieldcontainer',
            layout: 'hbox',
            style: {
                marginTop: '5px',
                paddingBottom: '5px',
                borderBottom: '1px dashed #b1b1b1;'
            },
            items: [
                {
                    xtype: 'combo',
                    name: 'property',
                    value: propertyTypeValue,
                    fieldLabel: t('Property'),
                    displayField: 'label',
                    valueField: 'key',
                    labelAlign: 'left',
                    queryMode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    allowBlank: true,
                    style: 'margin: 0 10px 0 0',
                    flex: 1,
                    listeners: {
                        change: function (cb, value) {
                            var fieldContainer = cb.up('fieldcontainer'),
                                propertyType = fieldContainer.down('fieldcontainer');
                            propertyType.removeAll(true, true);
                            propertyType.add(this.getOgContentFieldBasedOnType(value));
                        }.bind(this)
                    },
                    store: propertyTypeStore
                },
                {
                    xtype: 'fieldcontainer',
                    label: false,
                    style: 'margin: 0 10px 0 0',
                    flex: 3,
                    autoWidth: true,
                    items: [
                        field
                    ]
                },
                {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_delete',
                    width: 50,
                    listeners: {
                        click: function (btn) {
                            this.removeFieldCallback.call(this, btn, this.id);
                            this.refreshFieldCallback.call(this);
                        }.bind(this)
                    }
                }
            ]
        };
    },

    getOgContentFieldBasedOnType: function (propertyTypeValue) {

        var lfExtension;

        if (propertyTypeValue === 'og:type') {
            return this.generateOgTypeField();
        } else if (propertyTypeValue === 'og:image') {
            return this.generateOgImageField();
        }

        if (this.configuration.useLocalizedFields === false) {
            return this.generateOgContentField(propertyTypeValue, false, false, null);
        }

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension();

        var params = {
            showFieldLabel: true,
            fieldLabel: 'Content',
            gridWidth: 400,
            editorWindowWidth: 700,
            editorWindowHeight: 300,
            onGridRefreshRequest: function () {
                this.refreshFieldCallback.call(this)
            }.bind(this),
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.generateOgContentField.bind(this, propertyTypeValue, true, true)
        };

        return lfExtension.generateLocalizedField(params);
    },

    generateOgTypeField: function () {

        var typeStore = new Ext.data.ArrayStore({
            fields: ['label', 'key'],
            data: this.configuration.hasOwnProperty('ogTypes') ? this.configuration.ogTypes : []
        });

        return {
            xtype: 'combo',
            name: 'value',
            value: this.getStoredValue('value', null),
            fieldLabel: t('OG Type'),
            displayField: 'label',
            valueField: 'key',
            labelAlign: 'left',
            queryMode: 'local',
            triggerAction: 'all',
            editable: false,
            allowBlank: true,
            width: 400,
            store: typeStore
        }
    },

    generateOgImageField: function () {

        var fieldConfig,
            hrefField,
            storagePathHref,
            value = this.getStoredValue('value', null);

        fieldConfig = {
            label: t('Asset Path'),
            id: 'value',
            config: {
                types: ['asset'],
                subtypes: {asset: ['image']}
            }
        };

        hrefField = new Seo.MetaData.Extension.HrefFieldExtension(fieldConfig, value, null);
        storagePathHref = hrefField.getHref();

        storagePathHref.on({
            change: function () {
                this.refreshFieldCallback.call(this);
            }.bind(this)
        });

        return storagePathHref;
    },

    generateOgContentField: function (type, returnAsArray, isProxy, locale) {

        var value = this.getStoredValue('value', locale),
            field = {
            xtype: 'textfield',
            fieldLabel: type,
            width: 400,
            name: 'value',
            value: value,
            enableKeyEvents: true,
            listeners: isProxy ? {} : {
                keyup: function () {
                    this.refreshFieldCallback.call(this)
                }.bind(this)
            }
        };

        return returnAsArray ? [field] : field;
    },

    onLocalizedGridStoreRequest: function () {
        return [
            {
                title: 'Content',
                storeIdentifier: 'value',
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue('value', locale);
                }.bind(this)
            }
        ];
    },

    getStoredValue: function (name, locale) {

        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        return this.integratorValueFetcher.fetch(name, locale);
    },

    getValues: function () {

        var formValues;

        if (this.form === null) {
            return null;
        }

        formValues = this.form.getForm().getValues();

        if (!formValues.hasOwnProperty('value')) {
            return null;
        }

        if (formValues.value === null || formValues.value === '') {
            return null;
        }

        return formValues;
    },

    getValuesForPreview: function () {

        var locales;

        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        locales = Ext.isArray(pimcore.settings.websiteLanguages) ? pimcore.settings.websiteLanguages : ['en'];

        return {
            property: this.integratorValueFetcher.fetchForPreview('property', null),
            value: this.integratorValueFetcher.fetchForPreview('value', locales[0])
        };

    }
});