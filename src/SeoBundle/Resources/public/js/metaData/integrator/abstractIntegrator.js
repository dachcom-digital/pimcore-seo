pimcore.registerNS('Seo.MetaData.Integrator.AbstractIntegrator');
Seo.MetaData.Integrator.AbstractIntegrator = Class.create({

    type: null,
    configuration: null,
    data: null,

    initialize: function (type, configuration, data) {
        this.type = type;
        this.configuration = configuration;
        this.data = data;
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

    /**
     * @abstract
     */
    getValues: function () {
        return [];
    }
});