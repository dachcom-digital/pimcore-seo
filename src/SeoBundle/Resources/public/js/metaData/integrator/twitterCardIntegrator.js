pimcore.registerNS('Seo.MetaData.Integrator.TwitterCardIntegrator');
Seo.MetaData.Integrator.TwitterCardIntegrator = Class.create(Seo.MetaData.Integrator.AbstractPropertyIntegrator, {

    fieldSetTitle: t('Twitter Card'),
    iconClass: 'seo_integrator_icon_twitter',
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