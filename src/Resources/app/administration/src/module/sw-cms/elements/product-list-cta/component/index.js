import template from './sw-cms-el-product-list-cta.html.twig';
import './sw-cms-el-product-list-cta.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-product-list-cta', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        containerStyles() {
            return {
                'text-align': this.element.config.titlePosition.value,
                'justify-content': this.element.config.horizontalAlign.value,
                'display': 'flex',
                'flex-direction': 'column',
                'height': '100%',
                'width': '100%'
            };
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('product-list-cta');
        }
    }
});