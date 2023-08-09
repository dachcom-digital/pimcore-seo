class SeoCore {
    constructor() {
        this.ready = false;
        this.configuration = {};
        this.dataQueue = [];

        if (!String.prototype.format) {
            String.prototype.format = function () {
                const args = arguments;
                return this.replace(/{(\d+)}/g, function (match, number) {
                    return typeof args[number] != 'undefined'
                        ? args[number]
                        : match
                        ;
                });
            };
        }
    }

    getClassName() {
        return 'pimcore.plugin.Seo';
    }

    init() {
        Ext.Ajax.request({
            url: '/admin/seo/meta-data/get-meta-definitions',
            success: function (response) {

                const resp = Ext.decode(response.responseText);

                this.ready = true;
                this.configuration = resp.configuration;
                this.processQueue();

            }.bind(this)
        });
    }

    postOpenDocument(ev) {

        const document = ev.detail.document;

        if (this.ready) {
            this.processElement(document, 'page');
        } else {
            this.addElementToQueue(document, 'page');
        }
    }

    postOpenObject(ev) {

        const object = ev.detail.object;

        if (this.ready) {
            this.processElement(object, 'object');
        } else {
            this.addElementToQueue(object, 'object');
        }
    }

    postSaveDocument(ev) {

        const document = ev.detail.document;

        if (document.hasOwnProperty('seoPanel')) {
            document.seoPanel.save();
        }
    }

    postSaveObject(ev) {

        const object = ev.detail.object;

        if (object.hasOwnProperty('seoPanel')) {
            object.seoPanel.save();
        }
    }

    addElementToQueue(obj, type) {
        this.dataQueue.push({'obj': obj, 'type': type});
    }

    processQueue() {

        if (this.dataQueue.length > 0) {
            return;
        }

        Ext.each(this.dataQueue, function (data) {

            const obj = data.obj;
            const type = data.type;

            this.processElement(obj, type);

        }.bind(this));

        this.dataQueue = {};
    }

    processElement(obj, type) {

        if (type === 'object'
            && this.configuration.objects.enabled === true
            && this.configuration.objects.data_classes.indexOf(obj.data.general.o_className) !== -1) {
            obj.seoPanel = new Seo.MetaData.ObjectMetaDataPanel(obj, this.configuration);
            obj.seoPanel.setup(type);
        } else if (type === 'page'
            && this.configuration.documents.enabled === true
            && ['page'].indexOf(obj.type) !== -1) {
            obj.seoPanel = new Seo.MetaData.DocumentMetaDataPanel(obj, this.configuration);
            obj.seoPanel.setup(type, this.configuration.documents.hide_pimcore_default_seo_panel);
        }
    }

}

const seoCoreHandler = new SeoCore();

document.addEventListener(pimcore.events.pimcoreReady, seoCoreHandler.init.bind(seoCoreHandler));
document.addEventListener(pimcore.events.postOpenDocument, seoCoreHandler.postOpenDocument.bind(seoCoreHandler));
document.addEventListener(pimcore.events.postOpenObject, seoCoreHandler.postOpenObject.bind(seoCoreHandler));
document.addEventListener(pimcore.events.postSaveDocument, seoCoreHandler.postSaveDocument.bind(seoCoreHandler));
document.addEventListener(pimcore.events.postSaveObject, seoCoreHandler.postSaveObject.bind(seoCoreHandler));
