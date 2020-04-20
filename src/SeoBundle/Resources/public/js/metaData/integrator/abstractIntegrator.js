pimcore.registerNS('Seo.MetaData.Integrator.AbstractIntegrator');
Seo.MetaData.Integrator.AbstractIntegrator = Class.create({

    fieldSetTitle: 'Abstract Integrator',
    iconClass: false,

    previewContainerIsLoading: false,
    elementType: null,
    elementId: null,

    type: null,
    configuration: null,
    data: null,

    formPanel: null,
    fieldSet: null,
    previewContainerItem: null,
    previewContainerTemplate: null,
    delayedRefreshTask: null,
    renderAsTab: false,
    isInShutDownMode: false,

    initialize: function (elementType, elementId, type, configuration, data, renderAsTab) {
        this.elementType = elementType;
        this.elementId = elementId;
        this.type = type;
        this.configuration = configuration;
        this.data = data;
        this.renderAsTab = renderAsTab;
        this.delayedRefreshTask = new Ext.util.DelayedTask(this.refreshLivePreview.bind(this));
    },

    getType: function () {
        return this.type;
    },

    getConfiguration: function () {
        return this.configuration;
    },

    hasData: function () {
        return this.data !== null;
    },

    isEmptyValue: function (value) {
        return value === '' || value === null;
    },

    hasLivePreview: function () {
        var configuration = this.getConfiguration();

        if (configuration === null) {
            return false;
        }

        if (!configuration.hasOwnProperty('hasLivePreview')) {
            return false;
        }

        return configuration.hasLivePreview === true;
    },

    getLivePreviewTemplates: function () {
        var configuration = this.getConfiguration();

        if (configuration === null) {
            return null;
        }

        if (!configuration.hasOwnProperty('livePreviewTemplates')) {
            return null;
        }

        if (!Ext.isArray(configuration.livePreviewTemplates) || configuration.livePreviewTemplates.length === 0) {
            return null;
        }

        return configuration.livePreviewTemplates;
    },

    /**
     * @abstract
     */
    isCollapsed: function () {
        return false;
    },

    /**
     * @abstract
     */
    getValues: function (fieldSetTitle) {
        return [];
    },

    /**
     * @abstract
     */
    getValuesForPreview: function () {
        return [];
    },

    /**
     * @abstract
     */
    getStoredValue: function (name, locale) {
        return null;
    },

    /**
     * @abstract
     */
    buildLayout: function () {

        var panelItems;

        this.formPanel = new Ext.form.Panel({
            title: this.renderAsTab ? this.fieldSetTitle : false,
            iconCls: this.renderAsTab ? this.iconClass : false,
            style: {
                padding: this.renderAsTab ? '20px' : 0
            }
        });

        this.fieldSet = new Ext.form[this.renderAsTab ? 'Panel' : 'FieldSet']({
            title: this.renderAsTab ? false : this.fieldSetTitle,
            iconCls: this.renderAsTab ? false : this.iconClass,
            layout: {
                type: 'hbox'
            },
            collapsible: !this.renderAsTab,
            collapsed: this.renderAsTab ? false : this.isCollapsed(),
            defaults: {
                labelWidth: 200
            }
        });

        panelItems = [{
            xtype: 'panel',
            flex: 4,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: this.buildPanel()
        }];

        panelItems = this.generateLivePreviewPanel(panelItems);

        this.fieldSet.add(panelItems);

        this.formPanel.add(this.fieldSet);
        this.formPanel.on('destroy', function () {
            this.isInShutDownMode = true;
        }.bind(this));

        return this.formPanel;
    },

    /**
     * @internal
     */
    generateLivePreviewPanel: function (panelItems) {

        var tbar = null,
            livePreviewTemplates = this.getLivePreviewTemplates();

        if (!this.hasLivePreview()) {
            return panelItems;
        }

        if (livePreviewTemplates !== null) {

            this.previewContainerTemplate = livePreviewTemplates.length === 1 ? livePreviewTemplates[0][0] : null;

            tbar = [{
                xtype: 'combo',
                fieldLabel: t('Preview Template'),
                mode: 'local',
                editable: false,
                value: this.previewContainerTemplate,
                triggerAction: 'all',
                itemId: 'previewTemplateSelector',
                listeners: {
                    change: function (cb) {
                        var iframeComp = cb.up('panel').getComponent('previewContainer'),
                            el = iframeComp.getEl();

                        iframeComp.setLoading(true);

                        this.previewContainerTemplate = cb.getValue();
                        el.dom.src = this.getIframeUrl();
                        el.dom.onload = function () {
                            iframeComp.setLoading(false);
                        };

                    }.bind(this)
                },
                store: livePreviewTemplates
            }];
        }

        panelItems.push({
            xtype: 'splitter',
            cls: 'pimcore_main_splitter',
            collapseOnDblClick: false,
            collapsible: false,
            tracker: {
                tolerance: 0
            }
        });

        panelItems.push({
            xtype: 'panel',
            flex: 2,
            isFormItem: false,
            tbar: tbar,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            tbarCfg: {
                autoHeight: true
            },
            items: [
                {
                    xtype: 'component',
                    itemId: 'previewContainer',
                    autoEl: {
                        height: 400,
                        tag: 'iframe',
                        src: this.getIframeUrl(),
                        frameborder: 0,
                    }
                }
            ],
        });

        return panelItems;
    },

    refreshLivePreviewDelayed: function () {
        this.delayedRefreshTask.delay(800);
    },

    refreshLivePreview: function () {

        var iframeEl,
            previewContainerItems;

        // prevent delayed tasks from other components coming in too late.
        if (this.isInShutDownMode === true) {
            return;
        }

        if (this.previewContainerIsLoading === true) {
            return;
        }

        this.previewContainerIsLoading = true;
        if (this.previewContainerItem === null) {
            previewContainerItems = this.fieldSet.query('[itemId="previewContainer"]');
            if (previewContainerItems.length === 1) {
                this.previewContainerItem = previewContainerItems[0];
            }
        }

        if (this.previewContainerItem === null) {
            return;
        }

        iframeEl = this.previewContainerItem.getEl();
        if (iframeEl === null) {
            return;
        }

        this.previewContainerItem.setLoading(true);

        iframeEl.dom.src = this.getIframeUrl();
        iframeEl.dom.onload = function () {
            this.previewContainerItem.setLoading(false);
            this.previewContainerIsLoading = false;
        }.bind(this);
    },

    getIframeUrl: function () {

        var formData = this.getValuesForPreview();

        if (!Ext.isObject(formData)) {
            formData = {};
        }

        var data = {
            data: Ext.encode(formData),
            elementType: this.elementType,
            elementId: this.elementId,
            template: this.previewContainerTemplate,
            integratorName: this.type
        };

        var query = Ext.urlEncode(data);

        return '/admin/seo/meta-data/generate-meta-data-preview?' + query;
    }
});