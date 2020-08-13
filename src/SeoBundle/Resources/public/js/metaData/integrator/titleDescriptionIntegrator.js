pimcore.registerNS('Seo.MetaData.Integrator.TitleDescriptionIntegrator');
Seo.MetaData.Integrator.TitleDescriptionIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.title_description.title'),
    iconClass: 'seo_integrator_icon_title_description',
    integratorValueFetcher: null,
    legacyDocumentTitleField: null,
    legacyDocumentDescriptionField: null,

    initLegacyDocumentFields: function (panel) {

        var documentPanel;

        if (this.elementType !== 'document') {
            return;
        }

        documentPanel = panel.up('panel[id=document_' + this.elementId + ']').query('tabpanel[cls~=seo-pimcore-legacy-tab-panel]');

        if (documentPanel.length === 0) {
            return;
        }

        documentPanel[0].items.each(function (tab) {
            if (tab.iconCls.indexOf('page_settings') !== -1) {
                this.legacyDocumentTitleField = new Ext.form.Hidden({
                    name: 'title',
                    value: this.getStoredValue('title', null)
                });
                this.legacyDocumentDescriptionField = new Ext.form.Hidden({
                    name: 'description',
                    value: this.getStoredValue('description', null)
                });
                tab.add([this.legacyDocumentTitleField, this.legacyDocumentDescriptionField]);
            }
        }.bind(this));
    },

    buildPanel: function () {

        var configuration = this.getConfiguration(),
            lfExtension, params;

        this.integratorValueFetcher = new Seo.MetaData.Extension.IntegratorValueFetcher();

        this.fieldSet.on('afterrender', this.refreshLivePreview.bind(this));
        this.fieldSet.on('afterrender', this.initLegacyDocumentFields.bind(this));

        if (configuration.useLocalizedFields === false) {
            return [{
                xtype: 'panel',
                layout: 'form',
                items: this.generateFields(false, null)
            }];
        }

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension(null);

        params = {
            showFieldLabel: false,
            onGridRefreshRequest: this.refreshLivePreviewDelayed.bind(this),
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.generateFields.bind(this, true)
        };

        return [lfExtension.generateLocalizedField(params)];
    },

    generateFields: function (isProxy, lfIdentifier, locale) {

        var titleValue = this.getStoredValue('title', locale),
            descriptionValue = this.getStoredValue('description', locale);

        return [
            {
                xtype: 'textarea',
                fieldLabel: t('seo_bundle.integrator.title_description.single_title') + ' (' + (titleValue !== null ? titleValue.length : 0) + ')',
                name: 'title',
                itemId: 'title',
                maxLength: 255,
                height: 60,
                value: titleValue,
                enableKeyEvents: true,
                listeners: {
                    keyup: function (el) {
                        el.labelEl.update(t('seo_bundle.integrator.title_description.single_title') + ' (' + el.getValue().length + ')');
                        if (!isProxy) {
                            this.refreshLivePreviewDelayed()
                        }
                    }.bind(this),
                    change: function (el, value) {
                        if (this.legacyDocumentTitleField !== null) {
                            this.legacyDocumentTitleField.setValue(value);
                        }
                    }.bind(this),
                }
            },
            {
                xtype: 'textarea',
                fieldLabel: t('seo_bundle.integrator.title_description.single_description') + ' (' + (descriptionValue !== null ? descriptionValue.length : 0) + ')',
                maxLength: 350,
                height: 60,
                name: 'description',
                itemId: 'description',
                value: descriptionValue,
                enableKeyEvents: true,
                listeners: {
                    keyup: function (el) {
                        el.labelEl.update(t('seo_bundle.integrator.title_description.single_description') + ' (' + el.getValue().length + ')');
                        if (!isProxy) {
                            this.refreshLivePreviewDelayed()
                        }
                    }.bind(this),
                    change: function (el, value) {
                        if (this.legacyDocumentDescriptionField !== null) {
                            this.legacyDocumentDescriptionField.setValue(value);
                        }
                    }.bind(this),
                }
            }
        ];
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {

        return [
            {
                title: t('seo_bundle.integrator.title_description.single_title'),
                storeIdentifier: 'title',
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue('title', locale);
                }.bind(this)
            },
            {
                title: t('seo_bundle.integrator.title_description.single_description'),
                storeIdentifier: 'description',
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue('description', locale);
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

        if (this.formPanel === null) {
            return {};
        }

        formValues = this.formPanel.form.getValues();

        return formValues;
    },

    getValuesForPreview: function () {

        var locales;

        if (this.integratorValueFetcher === null) {
            return null;
        }

        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        locales = Ext.isArray(pimcore.settings.websiteLanguages) ? pimcore.settings.websiteLanguages : ['en'];

        return {
            title: this.integratorValueFetcher.fetchForPreview('title', locales[0]),
            description: this.integratorValueFetcher.fetchForPreview('description', locales[0])
        };
    }
});