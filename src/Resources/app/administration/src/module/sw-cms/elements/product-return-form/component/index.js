import template from './sw-cms-el-product-return-form.html.twig';
import './sw-cms-el-product-return-form.scss';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-el-product-return-form', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    computed: {
        title() {
            return this.element?.config?.title?.value || '';
        },

        subtitle() {
            return this.element?.config?.subtitle?.value || '';
        },

        showLabel() {
            return this.element?.config?.showLabel?.value ?? true;
        },

        showSalutation() {
            return this.element?.config?.showSalutation?.value ?? false;
        },

        showPhone() {
            return this.element?.config?.showPhone?.value ?? true;
        },

        showProductNumber() {
            return this.element?.config?.showProductNumber?.value ?? true;
        },

        showReason() {
            return this.element?.config?.showReason?.value ?? true;
        },

        showDescription() {
            return this.element?.config?.showDescription?.value ?? true;
        },

        buttonText() {
            return this.element?.config?.buttonText?.value || 'Submit return';
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('product-return-form');
        },
    },
});

