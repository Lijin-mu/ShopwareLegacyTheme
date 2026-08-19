import template from './sw-cms-preview-product-list.html.twig';
import './sw-cms-preview-product-list.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-product-list', {
    template,
    
    computed: {
    assetFilter() {
      return Shopware.Filter.getByName("asset");
    },
  },
});