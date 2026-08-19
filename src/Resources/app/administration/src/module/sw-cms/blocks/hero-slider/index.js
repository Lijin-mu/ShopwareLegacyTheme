import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'hero-slider',
    label: 'sw-cms.blocks.hero-slider.label',
    category: 'legacy',
    component: 'sw-cms-block-hero-slider',
    previewComponent: 'sw-cms-preview-hero-slider',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        slot01: 'cta',
        slot02: 'cta',
        slot03: 'cta',
        slot04: 'cta',
        slot05: 'cta',
        slot06: 'cta'
    }
});