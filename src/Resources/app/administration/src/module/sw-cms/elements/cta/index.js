import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'cta',
    label: 'sw-cms.elements.cta.label',
    component: 'sw-cms-el-cta',
    configComponent: 'sw-cms-el-config-cta',
    previewComponent: 'sw-cms-el-preview-cta',
    defaultConfig: {
        mediaDesktop: { source: 'static', value: null, entity: 'media' },
        mediaTablet: { source: 'static', value: null, entity: 'media' },
        mediaMobile: { source: 'static', value: null, entity: 'media' },
        
        title: { source: 'static', value: '' },
        titleHeading: { source: 'static', value: 'h2' },
        subTitle: { source: 'static', value: '' },
        subTitleHeading: { source: 'static', value: 'h3' },
        shortDescription: { source: 'static', value: '' },
        
        content: { source: 'static', value: '' },
        
        url1: { source: 'static', value: null },
        newTab1: { source: 'static', value: false },
        buttonText1: { source: 'static', value: '' },
        buttonType1: { source: 'static', value: 'primary' },
        
        buttomLevel1: { source: 'static', value: false },
        url2: { source: 'static', value: null },
        newTab2: { source: 'static', value: false },
        buttonText2: { source: 'static', value: '' },
        buttonType2: { source: 'static', value: 'primary' },

        buttomLevel2: { source: 'static', value: false },
        url3: { source: 'static', value: null },
        newTab3: { source: 'static', value: false },
        buttonText3: { source: 'static', value: '' },
        buttonType3: { source: 'static', value: 'primary' },

        titlePosition: { source: 'static', value: 'start' },
        alignment: { source: 'static', value: 'start' },
        customClass: { source: 'static', value: '' },
        active: { source: 'static', value: false },
        lazyLoad: { source: 'static', value: true }
    }
});