pimcore.registerNS('Seo.MetaData.Integrator.SchemaIntegrator');
Seo.MetaData.Integrator.SchemaIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('Schema Blocks'),
    schemaPanel: null,

    isCollapsed: function () {
        return !this.hasData();
    },

    buildPanel: function () {

        this.schemaPanel = new Ext.Panel({
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

        if (!Ext.isArray(this.data)) {
            return;
        }

        Ext.Array.each(this.data, function (SchemaValue) {
            this.addSchemaField(SchemaValue);
        }.bind(this));
    },

    getAddControl: function () {

        var items = [],
            configuration = this.getConfiguration(),
            disabled = false;

        if (configuration.hasOwnProperty('hasDynamicallyAddedJsonLdData')) {
            disabled = configuration.hasDynamicallyAddedJsonLdData;
        }

        items.push({
            cls: 'pimcore_block_button_plus',
            text: 'Add Schema Field',
            iconCls: 'pimcore_icon_plus',
            disabled: disabled,
            handler: this.addSchemaField.bind(this, null, null)
        });

        if (disabled === true) {
            items.push({
                xtype: 'label',
                text: t('Adding schema blocks manually has been disabled! This element is attached to a dynamic JSON-LD extractor.'),
                style: {
                    padding: '5px',
                    border: '1px solid #b32d2d',
                    display: 'inline-block',
                    background: '#e8acac',
                    margin: '0 0 10px 0',
                    color: 'black'
                }
            })
        }

        return new Ext.Toolbar({
            items: items
        });
    },

    addSchemaField: function (fieldValue) {

        var itemFieldContainer;

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
                {
                    xtype: 'textarea',
                    fieldLabel: t('Schema Data'),
                    style: 'margin: 0 10px 0 0',
                    name: 'schemas',
                    height: 200,
                    value: fieldValue,
                    inputAttrTpl: 'spellcheck="false"',
                    flex: 1,
                },
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

    removeSchemaField: function (btn) {
        this.schemaPanel.remove(btn.up('fieldcontainer'));
    },

    getValues: function () {

        var values = this.formPanel.getForm().getValues();

        if (!values.hasOwnProperty('schemas')) {
            return [];
        }

        if (Ext.isString(values.schemas)) {
            return [values.schemas];
        }

        if (!Ext.isArray(values.schemas)) {
            return [];
        }

        return values.schemas;
    },

    getValuesForPreview: function () {
        return [];
    }
});