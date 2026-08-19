import template from './sw-cms-block-product-return-form.html.twig';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-block-product-return-form', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
    ],
});

