import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'enquiry-form',
    label: 'sw-cms.blocks.form.enquiryForm.label',
    category: 'form',
    component: 'sw-cms-block-enquiry-form',
    previewComponent: 'sw-cms-preview-enquiry-form',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: {
            type: 'enquiry-form',
            default: {
                config: {
                    title: {
                        source: 'static',
                        value: 'Contact Us'
                    },
                    subtitle: {
                        source: 'static',
                        value: 'We would love to hear from you'
                    },
                    showLabel: {
                        source: 'static',
                        value: true
                    },
                    showSalutation: {
                        source: 'static',
                        value: true
                    },
                    showPhone: {
                        source: 'static',
                        value: true
                    },
                    showDescription: {
                        source: 'static',
                        value: true
                    },
                    buttonText: {
                        source: 'static',
                        value: 'Submit Enquiry'
                    },
                    successMessage: {
                        source: 'static',
                        value: 'Thank you for your enquiry!'
                    }
                }
            }
        }
    }
});

