import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'column-cta',
    label: 'sw-cms.blocks.column-cta.label',
    category: 'legacy',
    component: 'sw-cms-block-column-cta',
    previewComponent: 'sw-cms-preview-column-cta',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        slotHeader: 'text',
        slot00: 'cta',
        slot01: 'cta',
        slot02: 'cta',
        slot03: 'cta',
        slot04: 'cta',
        slot05: 'cta',
        slot06: 'cta',
        slot07: 'cta',
        slot08: 'cta',
        slot09: 'cta',
        slotFooter: 'text',
    }
});