pimcore.registerNS('Seo.MetaData.Integrator.TwitterCardIntegrator');
Seo.MetaData.Integrator.TwitterCardIntegrator = Class.create(Seo.MetaData.Integrator.AbstractPropertyIntegrator, {

    fieldSetTitle: 'Twitter Card',
    fieldType: 'name',
    fieldTypeProperty: 'twitter:card',
    imageAwareTypes: ['twitter:image'],
    previewFields: {
        'twitter:description' : 'description',
        'twitter:title' : 'title',
        'twitter:image' : 'image',
    },
    addFieldButtonLabel: t('Add Twitter Field')
});