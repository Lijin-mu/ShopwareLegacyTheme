import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'enquiry-form',
    label: 'sw-cms.elements.enquiryForm.label',
    component: 'sw-cms-el-enquiry-form',
    configComponent: 'sw-cms-el-config-enquiry-form',
    previewComponent: 'sw-cms-el-preview-enquiry-form',
    defaultConfig: {
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
});

