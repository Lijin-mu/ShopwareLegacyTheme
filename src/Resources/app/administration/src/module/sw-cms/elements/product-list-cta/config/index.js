import template from './sw-cms-el-config-product-list-cta.html.twig';

const { Component, Mixin } = Shopware;
const { EntityCollection, Criteria } = Shopware.Data;

Component.register('sw-cms-el-config-product-list-cta', {
    template,

    inject: ['repositoryFactory'],

    mixins: [Mixin.getByName('cms-element')],

    data() {
        return {
            productCollection: null
        };
    },

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },

        productCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('cover');
            criteria.addAssociation('options.group');
            return criteria;
        },

        productMultiSelectContext() {
            const context = { ...Shopware.Context.api };
            context.inheritance = true;
            return context;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('product-list-cta');

            this.productCollection = new EntityCollection(
                '/product',
                'product',
                Shopware.Context.api
            );

            if (this.element.config.products.value.length > 0) {
                const criteria = new Criteria();
                criteria.addAssociation('cover');
                criteria.addAssociation('options.group');
                criteria.setIds(this.element.config.products.value);

                this.productRepository.search(criteria, { ...Shopware.Context.api, inheritance: true })
                    .then((result) => {
                        this.productCollection = result;
                    });
            }
        },

        onProductsChange() {
            this.element.config.products.value = this.productCollection.getIds();
        },

        isSelected(itemId) {
            return this.productCollection.has(itemId);
        }
    }
});