pimcore.registerNS('Seo.MetaData.Integrator.TitleDescriptionIntegrator');
Seo.MetaData.Integrator.TitleDescriptionIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    data: null,
    formPanel: null,
    fieldSet: null,

    buildLayout: function () {

        var configuration = this.getConfiguration(),
            url = configuration.hasOwnProperty('url') ? configuration.url : 'http://localhost';

        this.formPanel = new Ext.form.Panel({
            title: false,
            autoScroll: true,
        });

        this.fieldSet = new Ext.form.FieldSet({
            xtype: 'fieldset',
            title: t('title') + ', ' + t('description'),
            itemId: 'metaDataPanel',
            collapsible: true,
            defaultType: 'textarea',
            minHeight: 320,
            defaults: {
                labelWidth: 200
            },
            listeners: {
                afterrender: function (el) {
                    window.setTimeout(function () {
                        if (this.updatePreview() && el.getEl().getWidth() > 1350) {
                            el.getComponent('previewField').show();
                        }
                    }.bind(this), 1000);
                }.bind(this),
            },
            items: [
                {
                    fieldLabel: t('title') + ' (' + (this.hasData() ? this.data.title.length : 0) + ')',
                    name: 'title',
                    itemId: 'title',
                    maxLength: 255,
                    height: 60,
                    width: 700,
                    value: this.hasData() ? this.data.title : null,
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function (el) {
                            el.labelEl.update(t('title') + ' (' + el.getValue().length + '):');
                            this.updatePreview();
                        }.bind(this)
                    }
                },
                {
                    fieldLabel: t('description') + ' (' + (this.hasData() ? this.data.description.length : 0) + ')',
                    maxLength: 350,
                    height: 60,
                    width: 700,
                    name: 'description',
                    itemId: 'description',
                    value: this.hasData() ? this.data.description : null,
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function (el) {
                            el.labelEl.update(t('description') + ' (' + el.getValue().length + '):');
                            this.updatePreview();
                        }.bind(this)
                    }
                },
                {
                    xtype: 'container',
                    itemId: 'previewField',
                    cls: 'seo-module-title-description',
                    hidden: true,
                    html:
                        '<div class="entry desktop">' +
                        '<div class="title"></div>' +
                        '<div class="url">' + url + '</div>' +
                        '<div class="description"></div>' +
                        '</div>' +
                        '<div class="entry mobile">' +
                        '<div class="title"></div>' +
                        '<div class="url">' + url + '</div>' +
                        '<div class="description"></div>' +
                        '</div>'
                }
            ]
        });

        this.formPanel.add(this.fieldSet);

        return this.formPanel;
    },

    getValues: function () {
        return this.formPanel.form.getValues();
    },

    updatePreview: function () {

        var previewField = this.fieldSet.getComponent('previewField'),
            title = this.fieldSet.getComponent('title').getValue(),
            description = this.fieldSet.getComponent('description').getValue(),
            desktopTitleEl, stringParts, tmpString,
            desktopDescrEl, mobileTitleEl, mobileDescrEl;

        if (!title) {
            previewField.hide();
            return false;
        }

        if (this.fieldSet.getEl().getWidth() > 1350) {
            previewField.show();
        }

        desktopTitleEl = Ext.get(previewField.getEl().selectNode('.desktop .title'));
        desktopTitleEl.setHtml(title);

        stringParts = title.split(' ');
        while (desktopTitleEl.getWidth() >= 600) {
            stringParts.splice(-1, 1);
            tmpString = stringParts.join(' ') + ' ...';
            desktopTitleEl.setHtml(tmpString);
        }

        desktopDescrEl = previewField.getEl().selectNode('.desktop .description');
        Ext.fly(desktopDescrEl).setHtml(this.truncate(description, 160));

        mobileTitleEl = previewField.getEl().selectNode('.mobile .title');
        Ext.fly(mobileTitleEl).setHtml(this.truncate(title, 78));

        mobileDescrEl = previewField.getEl().selectNode('.mobile .description');
        Ext.fly(mobileDescrEl).setHtml(this.truncate(description, 130));

        return true;
    },

    truncate: function (text, n) {

        var subString;

        if (text.length <= n) {
            return text;
        }

        subString = text.substr(0, n - 1);
        return subString.substr(0, subString.lastIndexOf(' ')) + ' ...';
    }
});