pimcore.registerNS('Seo.MetaData.Integrator.PropertyIntegratorItem');
Seo.MetaData.Integrator.PropertyIntegratorItem = Class.create({

    id: null,
    data: null,
    fieldType: null,
    fieldTypeProperty: null,
    imageAwareTypes: [],
    configuration: null,
    availableLocales: null,
    removeFieldCallback: null,
    refreshFieldCallback: null,
    form: null,
    integratorValueFetcher: null,

    initialize: function (
        id,
        data,
        fieldType,
        fieldTypeProperty,
        imageAwareTypes,
        removeFieldCallback,
        refreshFieldCallback,
        configuration,
        availableLocales
    ) {
        this.id = id;
        this.data = data;
        this.fieldType = fieldType;
        this.fieldTypeProperty = fieldTypeProperty;
        this.imageAwareTypes = imageAwareTypes;
        this.removeFieldCallback = removeFieldCallback;
        this.refreshFieldCallback = refreshFieldCallback;
        this.configuration = configuration;
        this.availableLocales = availableLocales;
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

        this.form.add(this.getFieldContainer());

        return this.form;
    },

    getFieldContainer: function () {

        var propertyTypeStore,
            configuration = this.configuration,
            typeStoreValue = this.getStoredValue(this.fieldType, null),
            propertyTypeValue = typeStoreValue === null ? this.fieldTypeProperty : typeStoreValue,
            field = this.getContentFieldBasedOnType(propertyTypeValue, this.id),
            user = pimcore.globalmanager.get('user');

        propertyTypeStore = new Ext.data.ArrayStore({
            fields: ['label', 'key', 'xliffExportAware'],
            data: configuration.hasOwnProperty('properties') ? configuration.properties : []
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
                    name: this.fieldType,
                    value: propertyTypeValue,
                    fieldLabel: t(Ext.String.capitalize(this.fieldType)),
                    displayField: 'label',
                    valueField: 'key',
                    labelAlign: 'top',
                    queryMode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    allowBlank: true,
                    style: 'margin: 0 10px 0 0',
                    maxWidth: 250,
                    flex: 2,
                    store: propertyTypeStore,
                    listeners: {
                        change: function (cb, value) {
                            var fieldContainer = cb.up('fieldcontainer'),
                                propertyType = fieldContainer.down('fieldcontainer');
                            propertyType.removeAll(true, true);
                            propertyType.add(this.getContentFieldBasedOnType(value));
                        }.bind(this)
                    }
                },
                {
                    xtype: 'fieldcontainer',
                    label: false,
                    style: 'margin: 0 10px 0 0',
                    flex: 2,
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    items: [
                        field
                    ]
                },
                {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_delete',
                    width: 50,
                    hidden: !user.isAllowed('seo_bundle_remove_property'),
                    style: {
                        marginTop: '30px'
                    },
                    listeners: {
                        click: function (btn) {
                            this.removeFieldCallback.call(this, btn, this.id);
                        }.bind(this)
                    }
                }
            ]
        };
    },

    getContentFieldBasedOnType: function (propertyTypeValue) {

        var lfExtension, params;

        if (propertyTypeValue === this.fieldTypeProperty) {
            return this.generateTypeField();
        } else if (this.imageAwareTypes.indexOf(propertyTypeValue) !== -1) {
            return this.generateImageField();
        }

        if (this.configuration.useLocalizedFields === false) {
            return this.generateContentField(propertyTypeValue, false, false, null);
        }

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension(this.id, this.availableLocales);

        params = {
            showFieldLabel: true,
            fieldLabel: t('seo_bundle.integrator.property.label_content'),
            editorWindowWidth: 700,
            editorWindowHeight: 300,
            onGridRefreshRequest: function () {
                this.refreshFieldCallback.call(this)
            }.bind(this),
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.generateContentField.bind(this, propertyTypeValue, true, true)
        };


        return lfExtension.generateLocalizedField(params);
    },

    generateTypeField: function () {

        var typeStore = new Ext.data.ArrayStore({
            fields: ['label', 'key'],
            data: this.configuration.hasOwnProperty('types') ? this.configuration.types : []
        });

        return {
            xtype: 'combo',
            name: 'value',
            value: this.getStoredValue('value', null),
            fieldLabel: t('seo_bundle.integrator.property.label_type'),
            displayField: 'label',
            valueField: 'key',
            labelAlign: 'top',
            queryMode: 'local',
            triggerAction: 'all',
            editable: false,
            allowBlank: true,
            width: '100%',
            store: typeStore
        }
    },

    generateImageField: function () {

        var fieldConfig,
            hrefField,
            storagePathHref,
            value = this.getStoredValue('value', null);

        fieldConfig = {
            label: t('seo_bundle.integrator.property.asset_path'),
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

    generateContentField: function (type, returnAsArray, isProxy, lfIdentifier, locale) {

        var value = this.getStoredValue('value', locale),
            field = {
                xtype: 'textfield',
                fieldLabel: type,
                labelAlign: 'top',
                name: 'value',
                value: value,
                width: '100%',
                enableKeyEvents: true,
                listeners: isProxy ? {} : {
                    keyup: function () {
                        this.refreshFieldCallback.call(this)
                    }.bind(this)
                }
            };

        return returnAsArray ? [field] : field;
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {
        return [
            {
                title: t('seo_bundle.integrator.property.label_content'),
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

        var locales = Ext.isArray(this.availableLocales) ? this.availableLocales : ['en'],
            values = {};

        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        values[this.fieldType] = this.integratorValueFetcher.fetchForPreview(this.fieldType, null);
        values['value'] = this.integratorValueFetcher.fetchForPreview('value', locales[0]);

        return values;
    }
});