import template from './sw-cms-preview-hero-slider.html.twig';
import './sw-cms-preview-hero-slider.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-hero-slider', {
    template,
    
    computed: {
    assetFilter() {
      return Shopware.Filter.getByName("asset");
    },
  },
});