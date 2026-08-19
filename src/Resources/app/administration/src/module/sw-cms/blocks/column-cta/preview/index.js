import template from './sw-cms-preview-column-cta.html.twig';
import './sw-cms-preview-column-cta.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-column-cta', {
    template,
    
    computed: {
    assetFilter() {
      return Shopware.Filter.getByName("asset");
    },
  },
});