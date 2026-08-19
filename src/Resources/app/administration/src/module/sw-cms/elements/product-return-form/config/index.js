import template from './sw-cms-el-config-product-return-form.html.twig';

Shopware.Component.register('sw-cms-el-config-product-return-form', {
    template,

    mixins: [
        Shopware.Mixin.getByName('cms-element'),
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('product-return-form');
        },
    },
});

