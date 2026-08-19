import template from "./sw-cms-el-config-recently-viewed-product-slider.html.twig";
import "./sw-cms-el-config-recently-viewed-product-slider.scss";

const { Component, Mixin } = Shopware;

Component.register("sw-cms-el-config-recently-viewed-product-slider", {
  template,

  inject: ["feature"],

  mixins: [Mixin.getByName("cms-element")],
  created() {
    this.createdComponent();
  },

  methods: {
    createdComponent() {
      this.initElementConfig("recently-viewed-product-slider");
  },
}
});
