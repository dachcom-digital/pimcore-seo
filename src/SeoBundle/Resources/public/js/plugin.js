pimcore.registerNS('pimcore.plugin.Seo');

pimcore.plugin.Seo = Class.create({

    ready: false,
    configuration: null,
    dataQueue: [],

    getClassName: function () {
        return 'pimcore.plugin.Seo';
    },

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
        document.addEventListener(pimcore.events.postOpenObject, this.postOpenObject.bind(this));
        document.addEventListener(pimcore.events.postOpenDocument, this.postOpenDocument.bind(this));
        document.addEventListener(pimcore.events.postSaveDocument, this.postSaveDocument.bind(this));
        document.addEventListener(pimcore.events.postSaveObject, this.postSaveObject.bind(this));

        if (!String.prototype.format) {
            String.prototype.format = function () {
                var args = arguments;
                return this.replace(/{(\d+)}/g, function (match, number) {
                    return typeof args[number] != 'undefined'
                        ? args[number]
                        : match
                        ;
                });
            };
        }
    },

    uninstall: function () {
        // void
    },

    pimcoreReady: function (params, broker) {
        Ext.Ajax.request({
            url: '/admin/seo/meta-data/get-meta-definitions',
            success: function (response) {
                var resp = Ext.decode(response.responseText);

                this.ready = true;
                this.configuration = resp.configuration;
                this.processQueue();

            }.bind(this)
        });
    },

    postOpenDocument: function (e) {
        let doc = e.detail.document

        if (this.ready) {
            this.processElement(doc, 'page');
        } else {
            this.addElementToQueue(doc, 'page');
        }
    },

    postOpenObject: function (e) {
        let obj = e.detail.object;

        if (this.ready) {
            this.processElement(obj, 'object');
        } else {
            this.addElementToQueue(obj, 'object');
        }
    },

    postSaveDocument: function (doc, type, task, only) {

        if (doc.hasOwnProperty('seoPanel')) {
            doc.seoPanel.save();
        }
    },

    postSaveObject: function (obj, task, only) {

        if (obj.hasOwnProperty('seoPanel')) {
            obj.seoPanel.save();
        }
    },

    addElementToQueue: function (obj, type) {
        this.dataQueue.push({'obj': obj, 'type': type});
    },

    processQueue: function () {

        if (this.dataQueue.length > 0) {

            Ext.each(this.dataQueue, function (data) {

                var obj = data.obj,
                    type = data.type;

                this.processElement(obj, type);

            }.bind(this));

            this.dataQueue = {};
        }
    },

    processElement: function (obj, type) {
        if (type === 'object'
            && this.configuration.objects.enabled === true
            && this.configuration.objects.data_classes.indexOf(obj.data.general.className) !== -1) {
            obj.seoPanel = new Seo.MetaData.ObjectMetaDataPanel(obj, this.configuration);
            obj.seoPanel.setup(type);
        } else if (type === 'page'
            && this.configuration.documents.enabled === true
            && ['page'].indexOf(obj.type) !== -1) {
            obj.seoPanel = new Seo.MetaData.DocumentMetaDataPanel(obj, this.configuration);
            obj.seoPanel.setup(type, this.configuration.documents.hide_pimcore_default_seo_panel);
        }
    }

});

new pimcore.plugin.Seo()