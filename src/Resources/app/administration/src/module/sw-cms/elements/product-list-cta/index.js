import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'product-list-cta',
    label: 'sw-cms.elements.product-list-cta.label',
    component: 'sw-cms-el-product-list-cta',
    configComponent: 'sw-cms-el-config-product-list-cta',
    previewComponent: 'sw-cms-el-preview-product-list-cta',
    defaultConfig: {
        title: {
            source: 'static',
            value: ''
        },
        titleHeading: {
            source: 'static',
            value: 'h2'
        },
        subTitle: {
            source: 'static',
            value: ''
        },
        subTitleHeading: {
            source: 'static',
            value: 'h3'
        },
        shortDescription: {
            source: 'static',
            value: ''
        },
        titlePosition: {
            source: 'static',
            value: 'start'
        },

        url1: {
            source: 'static',
            value: ''
        },
        buttonText1: {
            source: 'static',
            value: ''
        },
        buttonType1: {
            source: 'static',
            value: 'primary'
        },
        newTab1: {
            source: 'static',
            value: false
        },
        products: {
            source: 'static',
            value: []
        },
        productsPerPage: {
            source: 'static',
            value: 4
        },
        displayMode: {
            source: 'static',
            value: 'standard'
        },
        boxLayout: {
            source: 'static',
            value: 'standard'
        },
        horizontalAlign: {
            source: 'static',
            value: 'center'
        },
        verticalAlign: {
            source: 'static',
            value: 'center'
        },
        countDesktop: {
            source: 'static',
            value: 3
        },
        countTablet: {
            source: 'static',
            value: 2
        },
        countMobile: {
            source: 'static',
            value: 1
        },

        autoPlay: {
            source: 'static',
            value: false
        },
        speed: {
            source: 'static',
            value: 800
        },
        navigation: {
            source: 'static',
            value: true
        },
        dots: {
            source: 'static',
            value: true
        },
        loop: {
            source: 'static',
            value: false
        },
        rotate: {
            source: 'static',
            value: false
        },
        border: {
            source: 'static',
            value: false
        },
        active: {
            source: 'static',
            value: true
        },
        lazyLoad: {
            source: 'static',
            value: true
        },
        customClass: {
            source: 'static',
            value: ''
        }
    }
});