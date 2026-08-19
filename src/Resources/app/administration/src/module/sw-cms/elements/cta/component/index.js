import template from './sw-cms-el-cta.html.twig';

Shopware.Component.register('sw-cms-el-cta', {
    template,
    mixins: [Shopware.Mixin.getByName('cms-element')],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cta');
            this.initElementData('cta');
        }
    }
});