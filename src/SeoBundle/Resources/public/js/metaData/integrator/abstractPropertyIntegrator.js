pimcore.registerNS('Seo.MetaData.Integrator.AbstractPropertyIntegrator');
Seo.MetaData.Integrator.AbstractPropertyIntegrator = Class.create(Seo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: null,
    fieldType: null,
    fieldTypeProperty: null,
    imageAwareTypes: [],
    fields: {},
    previewFields: {},
    addFieldButtonLabel: t('seo_bundle.integrator.property.add_field'),
    addPresetButtonLabel: t('seo_bundle.integrator.property.add_preset'),

    buildPanel: function () {

        this.fields = {};

        this.blockPanel = new Ext.Panel({
            title: false,
            autoScroll: false,
            border: false,
            items: [
                this.getAddControl()
            ]
        });

        this.setupStoredData();

        return [this.blockPanel];
    },

    setupStoredData: function () {

        var idAwareData = {};

        if (!Ext.isArray(this.data)) {
            return;
        }

        // transformData first: assign unique id to each property element
        // we don't save any extJs ids in db but here we need some to allocate the right property element.
        Ext.Array.each(this.data, function (fieldData) {
            var fieldId = Ext.id();
            idAwareData[fieldId] = fieldData;
        }.bind(this));

        this.data = idAwareData;

        Ext.Object.each(this.data, function (fieldId, fieldData) {
            this.addField(fieldId, fieldData);
        }.bind(this));
    },

    getAddControl: function () {

        var items = [],
            presetMenu = [],
            configuration = this.getConfiguration(),
            availableOgPresets = configuration.hasOwnProperty('presets') ? configuration.presets : [];

        items.push({
            cls: 'pimcore_block_button_plus',
            text: this.addFieldButtonLabel,
            iconCls: 'pimcore_icon_plus',
            handler: this.addField.bind(this, null, null)
        });

        if (availableOgPresets.length > 0) {

            Ext.Array.each(availableOgPresets, function (preset) {
                presetMenu.push({
                    text: preset.hasOwnProperty('label') ? preset.label : ('Preset ' + index),
                    iconCls: preset.hasOwnProperty('icon_class') ? preset.icon_class : 'pimcore_icon_brick',
                    handler: this.addPreset.bind(this, preset)
                });
            }.bind(this));

            items.push({
                cls: 'pimcore_block_button_plus',
                text: this.addPresetButtonLabel,
                iconCls: 'pimcore_icon_objectbricks',
                menu: presetMenu
            });
        }

        return new Ext.Toolbar({
            items: this.generateAdditionalToolbarElements(items)
        });
    },

    generateAdditionalToolbarElements: function (items) {
        return items;
    },

    addPreset: function (preset) {

        if (!preset.hasOwnProperty('fields')) {
            return;
        }

        if (!Ext.isArray(preset.fields) || preset.fields.length === 0) {
            return;
        }

        Ext.Array.each(preset.fields, function (fieldConfig) {

            var fieldId = Ext.id(),
                fieldData = {};

            if (fieldConfig.property !== null) {
                fieldData[this.fieldType] = fieldConfig.property;
            }

            if (fieldConfig.content !== null) {
                fieldData['value'] = fieldConfig.content;
            }

            this.addField(fieldId, fieldData);

        }.bind(this));
    },

    addField: function (fieldId, fieldData) {

        var elementItem,
            itemFieldContainer,
            assertedFieldId = fieldId ? fieldId : Ext.id();

        elementItem = new Seo.MetaData.Integrator.PropertyIntegratorItem(
            assertedFieldId,
            fieldData,
            this.fieldType,
            this.fieldTypeProperty,
            this.imageAwareTypes,
            this.onRemoveField.bind(this),
            this.refreshLivePreviewDelayed.bind(this),
            this.getConfiguration(),
            this.getAvailableLocales()
        );

        itemFieldContainer = elementItem.createItem();

        this.fields[assertedFieldId] = elementItem;

        itemFieldContainer.on('destroy', function () {
            this.refreshLivePreviewDelayed();
        }.bind(this));

        this.blockPanel.add(itemFieldContainer);
    },

    onRemoveField: function (btn, fieldId) {

        var panel = btn.up('panel');

        if (Ext.isObject(this.fields) && this.fields.hasOwnProperty(fieldId)) {
            delete this.fields[fieldId];
        }

        this.blockPanel.remove(panel);
    },

    getValues: function () {

        var values = [];

        if (!Ext.isObject(this.fields)) {
            return [];
        }

        Ext.Object.each(this.fields, function (fieldId, field) {
            var fieldValues = field.getValues();
            if (fieldValues !== null) {
                values.push(fieldValues);
            }
        });

        return values;
    },

    getValuesForPreview: function () {

        var values = {}, fieldType;

        Ext.Object.each(this.fields, function (fieldId, field) {
            var fieldValues = field.getValuesForPreview();
            if (fieldValues !== null) {
                fieldType = fieldValues[this.fieldType];
                if (this.previewFields.hasOwnProperty(fieldType)) {
                    values[this.previewFields[fieldType]] = fieldValues.value;
                }
            }
        }.bind(this));

        return values;
    }
});