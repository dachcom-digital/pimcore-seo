pimcore.registerNS('Seo.MetaData.ObjectMetaDataPanel');
Seo.MetaData.ObjectMetaDataPanel = Class.create(Seo.MetaData.AbstractMetaDataPanel, {

    elementType: null,

    setup: function (elementType) {
        this.elementType = elementType;

        this.buildSeoMetaDataTab();
        this.generateMetaDataFields();
    },

    getElementType: function () {
        return 'object';
    },

    getElementId: function () {
        return this.getElement().id;
    },

    generateMetaDataFields: function () {
        // tbd
    },
});