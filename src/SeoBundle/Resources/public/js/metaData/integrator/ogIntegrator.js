pimcore.registerNS('Seo.MetaData.Integrator.OpenGraphIntegrator');
Seo.MetaData.Integrator.OpenGraphIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: 'Open Graph Editor',

    ogFields: {},

    buildPanel: function () {

        this.ogFields = {};

        this.ogBlockPanel = new Ext.Panel({
            title: false,
            autoScroll: true,
            border: false,
            items: [
                this.getAddControl()
            ]
        });

        this.setupStoredData();

        return [this.ogBlockPanel];

    },

    setupStoredData: function () {

        var idAwareData = {};

        if (!Ext.isArray(this.data)) {
            return;
        }

        // transformData first: assign unique id to each og element
        // we don't save any extJs ids in db but here we need some to allocate the right og element.
        Ext.Array.each(this.data, function (ogFieldData) {
            var fieldId = Ext.id();
            idAwareData[fieldId] = ogFieldData;
        }.bind(this));

        this.data = idAwareData;

        Ext.Object.each(this.data, function (ogFieldId, ogFieldData) {
            this.addOgField(ogFieldId, ogFieldData);
        }.bind(this));
    },

    getAddControl: function () {

        var configuration = this.getConfiguration(),
            presetMenu = [],
            items = [],
            availableOgPresets = configuration.hasOwnProperty('presets') ? configuration.presets : [];

        items.push({
            cls: 'pimcore_block_button_plus',
            text: 'Add OG Field',
            iconCls: 'pimcore_icon_plus',
            handler: this.addOgField.bind(this, null, null)
        });

        if (availableOgPresets.length > 0) {

            Ext.Array.each(availableOgPresets, function (ogPreset) {
                presetMenu.push({
                    text: ogPreset.hasOwnProperty('label') ? ogPreset.label : ('Preset ' + index),
                    iconCls: ogPreset.hasOwnProperty('icon_class') ? ogPreset.icon_class : 'pimcore_icon_brick',
                    handler: this.addOgPreset.bind(this, ogPreset)
                });
            }.bind(this));

            items.push({
                cls: 'pimcore_block_button_plus',
                text: 'Add OG Preset',
                iconCls: 'pimcore_icon_objectbricks',
                menu: presetMenu
            });
        }

        return new Ext.Toolbar({
            items: items
        });
    },

    addOgPreset: function (ogPreset) {

        if (!ogPreset.hasOwnProperty('fields')) {
            return;
        }

        if (!Ext.isArray(ogPreset.fields) || ogPreset.fields.length === 0) {
            return;
        }

        Ext.Array.each(ogPreset.fields, function (ogFieldConfig) {

            var ogFieldId = Ext.id(),
                fieldData = {};

            if (ogFieldConfig.property !== null) {
                fieldData['property'] = ogFieldConfig.property;
            }

            if (ogFieldConfig.content !== null) {
                fieldData['value'] = ogFieldConfig.content;
            }

            this.addOgField(ogFieldId, fieldData);

        }.bind(this));
    },

    addOgField: function (fieldId, fieldData) {

        var elementItem,
            itemFieldContainer,
            ogFieldId = fieldId ? fieldId : Ext.id();

        elementItem = new Seo.MetaData.Integrator.OpenGraphIntegratorItem(
            ogFieldId,
            fieldData,
            this.onRemoveOgField.bind(this),
            this.refreshLivePreviewDelayed.bind(this),
            this.getConfiguration()
        );

        itemFieldContainer = elementItem.createItem();

        this.ogFields[ogFieldId] = elementItem;

        this.ogBlockPanel.add(itemFieldContainer);
    },

    onRemoveOgField: function (btn, ogFieldId) {

        var panel = btn.up('panel');

        if (Ext.isObject(this.ogFields) && this.ogFields.hasOwnProperty(ogFieldId)) {
            delete this.ogFields[ogFieldId];
        }

        this.ogBlockPanel.remove(panel);
    },

    getValues: function () {

        var values = [];

        if (!Ext.isObject(this.ogFields)) {
            return [];
        }

        Ext.Object.each(this.ogFields, function (ogFieldId, ogField) {
            var ogFieldValues = ogField.getValues();
            if (ogFieldValues !== null) {
                values.push(ogFieldValues);
            }
        });

        return values;
    },

    getValuesForPreview: function () {

        var values = {};

        Ext.Object.each(this.ogFields, function (ogFieldId, ogField) {
            var ogFieldValues = ogField.getValuesForPreview();

            if (ogFieldValues !== null) {
                if (ogFieldValues.property === 'og:description') {
                    values['description'] = ogFieldValues.value;
                } else if (ogFieldValues.property === 'og:title') {
                    values['title'] = ogFieldValues.value;
                } else if (ogFieldValues.property === 'og:image') {
                    values['image'] = ogFieldValues.value;
                }
            }
        });

        return values;
    }
});