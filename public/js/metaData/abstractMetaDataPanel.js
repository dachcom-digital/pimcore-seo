pimcore.registerNS('Seo.MetaData.AbstractMetaDataPanel');
Seo.MetaData.AbstractMetaDataPanel = Class.create({

    configuration: null,
    element: null,
    integrator: [],

    draftNode: null,
    layout: null,
    tabPanel: null,
    renderAsTab: false,

    initialize: function (element, configuration) {
        this.configuration = configuration;
        this.element = element;
        this.integrator = [];

        if (this.configuration.hasOwnProperty('integrator_rendering_type')) {
            this.renderAsTab = this.configuration.integrator_rendering_type === 'tab';
        }
    },

    getElement: function () {
        return this.element;
    },

    buildSeoMetaDataTab: function () {

        this.layout = new Ext.FormPanel({
            title: t('seo_bundle.panel_title'),
            iconCls: 'pimcore_material_icon seo_icon_meta_data',
            border: false,
            autoScroll: true,
            bodyStyle: this.renderAsTab ? 'padding: 10px;' : 'padding: 0 10px 0 10px;'
        });

        if (this.renderAsTab === true) {
            this.tabPanel = new Ext.TabPanel({
                activeTab: 0,
                layout: 'anchor',
                width: '100%',
                defaults: {
                    autoHeight: true,
                },
            });

            this.layout.add(this.tabPanel);
        }

        this.element.tabbar.add(this.layout);

        this.loadElementMetaData();

    },

    loadElementMetaData: function () {

        Ext.Ajax.request({
            url: '/admin/seo/meta-data/get-element-meta-data-configuration',
            params: {
                elementType: this.getElementType(),
                elementId: this.getElementId()
            },
            success: function (response) {
                var resp = Ext.decode(response.responseText);
                if (resp.success === false) {
                    Ext.Msg.alert(t('error'), resp.message);
                    return;
                }

                this.draftNode = new Ext.form.FieldContainer({
                    xtype: 'container',
                    flex: 1,
                    hidden: resp.isDraft === false,
                    html: t('seo_bundle.panel.draft_note'),
                    style: {
                        padding: '5px',
                        border: '1px solid #6428b4',
                        background: '#6428b45c',
                        margin: '0 0 10px 0',
                        color: 'black'
                    }
                });

                this.layout.insert(0, this.draftNode)

                this.buildMetaDataIntegrator(resp.data, resp.configuration, resp.availableLocales);

            }.bind(this),
            failure: function () {
                Ext.Msg.alert(t('error'), t('seo_bundle.panel.error_fetch_data'));
            }.bind(this)
        });

    },

    buildMetaDataIntegrator: function (data, configuration, availableLocales) {

        Ext.Array.each(this.configuration.enabled_integrator, function (integrator) {
            var integratorClass,
                integratorName = integrator['integrator_name'],
                integratorClassName = this.getIntegratorClassName(integratorName),
                integratorConfiguration = configuration !== null && configuration.hasOwnProperty(integratorName) ? configuration[integratorName] : null,
                integratorData = data !== null && data.hasOwnProperty(integratorName) ? data[integratorName] : null;

            if (Seo.MetaData.Integrator.hasOwnProperty(integratorClassName)) {
                integratorClass = new Seo.MetaData.Integrator[integratorClassName](this.getElementType(), this.getElementId(), integratorName, integratorConfiguration, availableLocales, integratorData, this.renderAsTab);
                this.integrator.push(integratorClass);
                this[this.renderAsTab === true ? 'tabPanel' : 'layout'].add(integratorClass.buildLayout());
            } else {
                console.warn('Integrator class Seo.MetaData.Integrator.' + integratorClassName + ' not found!');
            }
        }.bind(this));

        if (this.renderAsTab === true) {
            this.tabPanel.setActiveTab(0);
        }
    },

    save: function (task) {

        var integratorValues = this.getIntegratorValues();

        this.draftNode.setHidden(task === 'publish');

        Ext.Ajax.request({
            url: '/admin/seo/meta-data/set-element-meta-data-configuration',
            method: 'POST',
            params: {
                task: task,
                integratorValues: Ext.encode(integratorValues),
                elementType: this.getElementType(),
                elementId: this.getElementId()
            },
            success: function (response) {
                var resp = Ext.decode(response.responseText);
                if (resp.success === false) {
                    Ext.Msg.alert(t('error'), resp.message);
                }
            },
            failure: function (resp) {
                Ext.Msg.alert(t('error'), t('seo_bundle.panel.error_save_data'));
            }
        });
    },

    getIntegratorValues: function () {

        var values = {};
        Ext.Array.each(this.integrator, function (integrator) {
            values[integrator.getType()] = integrator.getValues();
        });

        return values;
    },

    getIntegratorClassName: function (integratorName) {

        var name = integratorName.replace(/(\_\w)/g, function (m) {
            return m[1].toUpperCase();
        });

        return ucfirst(name) + 'Integrator';
    }
});