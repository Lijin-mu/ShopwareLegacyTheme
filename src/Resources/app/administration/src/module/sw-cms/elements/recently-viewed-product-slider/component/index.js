import template from "./sw-cms-el-recently-viewed-product-slider.html.twig";
import "./sw-cms-el-recently-viewed-product-slider.scss";

const { Component, Mixin } = Shopware;

Component.register("sw-cms-el-recently-viewed-product-slider", {
  template,

  inject: ["feature"],

  mixins: [Mixin.getByName("cms-element")],

  data() {
    return {
      sliderBoxLimit: 3,
    };
  },

  computed: {
    demoProductElement() {
      return {
        config: {
          boxLayout: {
            source: "static",
            value: this.element.config.boxLayout.value,
          },
          displayMode: {
            source: "static",
            value: this.element.config.displayMode.value,
          },
        },
        data: null,
      };
    },
    hasNavigation() {
      return !!this.element.config.navigation.value;
    },
    classes() {
      return {
        "has--navigation": this.hasNavigation,
        "has--border": !!this.element.config.border.value,
      };
    },
    currentDeviceView() {
      return this.cmsPageState.currentCmsDeviceView;
    },
    verticalAlignStyle() {
      if (!this.element.config.verticalAlign.value) {
        return null;
      }
      return `align-self: ${this.element.config.verticalAlign.value};`;
    },
  },

  created() {
    this.createdComponent();
  },

  methods: {
    createdComponent() {
      this.initElementConfig("recently-viewed-product-slider");
      this.initElementData("recently-viewed-product-slider");
    },
  },
});
