import template from './sw-cms-el-enquiry-form.html.twig';
import './sw-cms-el-enquiry-form.scss';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-el-enquiry-form', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        title() {
            return this.element?.config?.title?.value || '';
        },

        subtitle() {
            return this.element?.config?.subtitle?.value || '';
        },

        showLabel(){
            return this.element?.config?.showLabel?.value ?? true;
        },

        showSalutation() {
            return this.element?.config?.showSalutation?.value ?? true;
        },

        showPhone() {
            return this.element?.config?.showPhone?.value ?? true;
        },

        showDescription() {
            return this.element?.config?.showDescription?.value ?? true;
        },

        buttonText() {
            return this.element?.config?.buttonText?.value || 'Submit Enquiry';
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('enquiry-form');
        }
    }
});

