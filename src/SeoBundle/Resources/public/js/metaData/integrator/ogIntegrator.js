pimcore.registerNS('Seo.MetaData.Integrator.OpenGraphIntegrator');
Seo.MetaData.Integrator.OpenGraphIntegrator = Class.create(Seo.MetaData.Integrator.AbstractPropertyIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.og.title'),
    iconClass: 'seo_integrator_icon_icon_og',
    fieldType: 'property',
    fieldTypeProperty: 'og:type',
    imageAwareTypes: ['og:image'],
    previewFields: {
        'og:description': 'description',
        'og:title': 'title',
        'og:image': 'image',
    },
    addFieldButtonLabel: t('seo_bundle.integrator.og.add_field'),

    generateAdditionalToolbarElements: function (items) {
        items.push({
            xtype: 'label',
            text: t('seo_bundle.integrator.og.url_note'),
            style: {
                padding: '5px',
                border: '1px solid #A4E8A6',
                display: 'inline-block',
                background: '#dde8c9',
                margin: '0 0 10px 0',
                color: 'black'
            }
        });

        return items;
    },

});