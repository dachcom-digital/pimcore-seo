pimcore.registerNS('Seo.MetaData.Extension.IntegratorValueFetcher');
Seo.MetaData.Extension.IntegratorValueFetcher = Class.create({

    storageData: null,
    editData: null,

    setStorageData: function (storageData) {
        this.storageData = storageData;
    },

    setEditData: function (editData) {
        this.editData = editData;
    },

    fetchForPreview: function (name, locale) {

        var localizedValue = null,
            value = this.fetch(name, locale);

        if (value === null) {
            return null;
        }

        if (!Ext.isArray(value)) {
            return value;
        }

        Ext.Array.each(value, function (localizedValueData) {
            if (localizedValueData.locale === locale) {
                localizedValue = localizedValueData.value;
                return false;
            }
        });

        return localizedValue;
    },

    fetch: function (name, locale) {

        var currentValue;

        if (typeof locale === 'undefined') {
            locale = null;
        }

        // first look up edit data
        currentValue = this.findValueInData(this.editData, name, locale);

        // if not found, check storage data
        if (currentValue === null) {
            currentValue = this.findValueInData(this.storageData, name, locale);
        }

        return currentValue;
    },


    /**
     * @internal
     */
    findValueInData: function (values, name, locale) {

        var currentValueData,
            storedValue = null;

        if (!values || !values.hasOwnProperty(name)) {
            return null;
        }

        currentValueData = values[name];
        if (Ext.isArray(currentValueData)) {
            Ext.Array.each(currentValueData, function (localizedValue) {
                if (localizedValue.locale === locale) {
                    storedValue = localizedValue.value;
                    return false;
                }
            });
        } else {
            return currentValueData;
        }

        return storedValue;
    }
});