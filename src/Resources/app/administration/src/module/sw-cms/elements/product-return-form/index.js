import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'product-return-form',
    label: 'sw-cms.elements.productReturnForm.label',
    component: 'sw-cms-el-product-return-form',
    configComponent: 'sw-cms-el-config-product-return-form',
    previewComponent: 'sw-cms-el-preview-product-return-form',
    defaultConfig: {
        title: {
            source: 'static',
            value: 'Start a return',
        },
        subtitle: {
            source: 'static',
            value: 'Here you can enter return information...',
        },
        showLabel: {
            source: 'static',
            value: true,
        },
        showSalutation: {
            source: 'static',
            value: false,
        },
        showPhone: {
            source: 'static',
            value: true,
        },
        showProductNumber: {
            source: 'static',
            value: true,
        },
        showReason: {
            source: 'static',
            value: true,
        },
        showDescription: {
            source: 'static',
            value: true,
        },
        buttonText: {
            source: 'static',
            value: 'Submit return',
        },
        successMessage: {
            source: 'static',
            value: 'We received your return request.',
        },
        reasonOptions: {
            source: 'static',
            value: 'Wrong size\nDamaged on arrival\nNot as described\nOther',
        },
    },
});

