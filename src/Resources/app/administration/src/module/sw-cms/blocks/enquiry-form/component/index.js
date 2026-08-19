import template from './sw-cms-block-enquiry-form.html.twig';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-block-enquiry-form', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ]
});
