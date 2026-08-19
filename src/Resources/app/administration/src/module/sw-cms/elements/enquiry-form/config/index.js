import template from './sw-cms-el-config-enquiry-form.html.twig';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-el-config-enquiry-form', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        title: {
            get() {
                return this.element?.config?.title?.value || '';
            },
            set(value) {
                if (this.element?.config?.title) {
                    this.element.config.title.value = value;
                }
            }
        },

        subtitle: {
            get() {
                return this.element?.config?.subtitle?.value || '';
            },
            set(value) {
                if (this.element?.config?.subtitle) {
                    this.element.config.subtitle.value = value;
                }
            }
        },

        showLabel:{
            get() {
                    return this.element?.config?.showLabel?.value || true;
                },
            set(value) {
                if (this.element?.config?.showLabel) {
                    this.element.config.showLabel.value = value;
                }
            }
        },
        

        showSalutation: {
            get() {
                return this.element?.config?.showSalutation?.value || true;
            },
            set(value) {
                if (this.element?.config?.showSalutation) {
                    this.element.config.showSalutation.value = value;
                }
            }
        },

        showPhone: {
            get() {
                return this.element?.config?.showPhone?.value || true;
            },
            set(value) {
                if (this.element?.config?.showPhone) {
                    this.element.config.showPhone.value = value;
                }
            }
        },

        showDescription: {
            get() {
                return this.element?.config?.showDescription?.value || true;
            },
            set(value) {
                if (this.element?.config?.showDescription) {
                    this.element.config.showDescription.value = value;
                }
            }
        },

        buttonText: {
            get() {
                return this.element?.config?.buttonText?.value || 'Submit Enquiry';
            },
            set(value) {
                if (this.element?.config?.buttonText) {
                    this.element.config.buttonText.value = value;
                }
            }
        },

        successMessage: {
            get() {
                return this.element?.config?.successMessage?.value || 'Thank you for your enquiry!';
            },
            set(value) {
                if (this.element?.config?.successMessage) {
                    this.element.config.successMessage.value = value;
                }
            }
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

