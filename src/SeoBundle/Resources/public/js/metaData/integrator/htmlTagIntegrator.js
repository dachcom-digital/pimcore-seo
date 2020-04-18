pimcore.registerNS('Seo.MetaData.Integrator.HtmlTagIntegrator');
Seo.MetaData.Integrator.HtmlTagIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('html_tags') + ' (&lt;meta .../&gt; &lt;link .../&gt; ...)',

    isCollapsed: function () {
        return !this.hasData();
    },

    buildPanel: function () {

        this.htmlTagPanel = new Ext.Panel({
            title: false,
            autoScroll: true,
            border: false,
            items: [
                {
                    xtype: 'label',
                    text: t('Adding raw tags is not recommend. Please use the Element above!'),
                    style: {
                        padding: '10px',
                        border: '1px solid #b32d2d',
                        display: 'inline-block',
                        background: '#e8acac',
                        margin: '0 0 10px 0',
                        color: 'black'
                    }
                },
                this.getAddControl()
            ]
        });

        this.setupStoredData();

        return [this.htmlTagPanel];
    },

    setupStoredData: function () {

        if (!Ext.isArray(this.data)) {
            return;
        }

        Ext.Array.each(this.data, function (htmlTagValue) {
            this.addHtmlTagField(htmlTagValue);
        }.bind(this));
    },

    getAddControl: function () {

        var items = [];

        items.push({
            cls: 'pimcore_block_button_plus',
            text: 'Add HTML Tag Field',
            iconCls: 'pimcore_icon_plus',
            handler: this.addHtmlTagField.bind(this, null, null)
        });

        return new Ext.Toolbar({
            items: items
        });
    },

    addHtmlTagField: function (fieldValue) {

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
                    xtype: 'textfield',
                    fieldLabel: t('Tag'),
                    style: 'margin: 0 10px 0 0',
                    name: 'tags',
                    value: fieldValue,
                    flex: 1,
                },
                {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_delete',
                    width: 50,
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