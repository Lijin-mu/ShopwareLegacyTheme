import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'product-list',
    label: 'sw-cms.blocks.product-list.label',
    category: 'legacy',
    component: 'sw-cms-block-product-list',
    previewComponent: 'sw-cms-preview-product-list',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        productList: 'product-list-cta',
    }
});