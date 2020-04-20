pimcore.registerNS('Seo.MetaData.Integrator.OpenGraphIntegrator');
Seo.MetaData.Integrator.OpenGraphIntegrator = Class.create(Seo.MetaData.Integrator.AbstractPropertyIntegrator, {

    fieldSetTitle: 'Open Graph Editor',
    fieldType: 'property',
    fieldTypeProperty: 'og:type',
    imageAwareTypes: ['og:image'],
    previewFields: {
        'og:description' : 'description',
        'og:title' : 'title',
        'og:image' : 'image',
    },
    addFieldButtonLabel: t('Add OG Field')
});