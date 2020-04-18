pimcore.registerNS('Seo.MetaData.Integrator.TitleDescriptionIntegrator');
Seo.MetaData.Integrator.TitleDescriptionIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('title') + ', ' + t('description'),

    integratorValueFetcher: null,

    buildPanel: function () {

        this.integratorValueFetcher = new Seo.MetaData.Extension.IntegratorValueFetcher();

        var configuration = this.getConfiguration(),
            lfExtension;

        this.fieldSet.on('afterrender', this.refreshLivePreview.bind(this));

        if (configuration.useLocalizedFields === false) {
            return [{
                xtype: 'panel',
                layout: 'form',
                items: this.generateFields(false, null)
            }];
        }

        lfExtension = new Seo.MetaData.Extension.LocalizedFieldExtension();

        var params = {
            showFieldLabel: false,
            onGridRefreshRequest: this.refreshLivePreviewDelayed.bind(this),
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.generateFields.bind(this, true)
        };

        return [lfExtension.generateLocalizedField(params)];

    },

    generateFields: function (isProxy, locale) {

        var titleValue = this.getStoredValue('title', locale),
            descriptionValue = this.getStoredValue('description', locale);

        return [
            {
                xtype: 'textarea',
                fieldLabel: t('title') + ' (' + (titleValue !== null ? titleValue.length : 0) + ')',
                name: 'title',
                itemId: 'title',
                maxLength: 255,
                height: 60,
                value: titleValue,
                enableKeyEvents: true,
                listeners: {
                    keyup: function (el) {
                        el.labelEl.update(t('title') + ' (' + el.getValue().length + ')');
                        if (!isProxy) {
                            this.refreshLivePreviewDelayed()
                        }
                    }.bind(this)
                }
            },
            {
                xtype: 'textarea',
                fieldLabel: t('description') + ' (' + (descriptionValue !== null ? descriptionValue.length : 0) + ')',
                maxLength: 350,
                height: 60,
                name: 'description',
                itemId: 'description',
                value: descriptionValue,
                enableKeyEvents: true,
                listeners: {
                    keyup: function (el) {
                        el.labelEl.update(t('description') + ' ('+ el.getValue().length + ')');
                        if (!isProxy) {
                            this.refreshLivePreviewDelayed()
                        }
                    }.bind(this)
                }
            }
        ];
    },

    onLocalizedGridStoreRequest: function () {

        return [
            {
                title: 'Title',
                storeIdentifier: 'title',
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue('title', locale);
                }.bind(this)
            },
            {
                title: 'Description',
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