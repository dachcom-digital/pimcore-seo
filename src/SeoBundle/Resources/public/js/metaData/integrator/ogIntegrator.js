pimcore.registerNS('Seo.MetaData.Integrator.OpenGraphIntegrator');
Seo.MetaData.Integrator.OpenGraphIntegrator = Class.create(Seo.MetaData.Integrator.AbstractPropertyIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.og.title'),
    iconClass: 'seo_integrator_icon_icon_og',
    fieldType: 'property',
    fieldTypeProperty: 'og:type',
    imageAwareTypes: ['og:image'],
    previewFields: {
        'og:description' : 'description',
        'og:title' : 'title',
        'og:image' : 'image',
    },
    addFieldButtonLabel: t('seo_bundle.integrator.og.add_field')
});