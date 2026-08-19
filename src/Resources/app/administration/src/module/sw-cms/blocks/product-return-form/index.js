import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'product-return-form',
    label: 'sw-cms.blocks.form.productReturnForm.label',
    category: 'form',
    component: 'sw-cms-block-product-return-form',
    previewComponent: 'sw-cms-preview-product-return-form',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        content: {
            type: 'product-return-form',
            default: {
                config: {
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
            },
        },
    },
});

