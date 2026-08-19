import "./component";
import "./config";
import "./preview";

Shopware.Service("cmsService").registerCmsElement({
  name: "recently-viewed-product-slider",
  label: "sw-cms.blocks.recently-viewed-product-slider.label",
  component: "sw-cms-el-recently-viewed-product-slider",
  configComponent: "sw-cms-el-config-recently-viewed-product-slider",
  previewComponent:
    "sw-cms-el-preview-recently-viewed-product-slider",
  defaultConfig: {
    displayMode: {
      source: "static",
      value: "standard",
    },
    boxLayout: {
      source: "static",
      value: "standard",
    },
    navigation: {
      source: "static",
      value: true,
    },
    rotate: {
      source: "static",
      value: false,
    },
    border: {
      source: "static",
      value: false,
    },
    verticalAlign: {
      source: "static",
      value: null,
    },
    countDesktop: {
      source: "static",
      value: 3,
    },
    countTablet: {
      source: "static",
      value: 2,
    },
    countMobile: {
      source: "static",
      value: 1,
    },
    autoPlay: {
      source: "static",
      value: false,
    },
    speed: {
      source: "static",
      value: 800,
    },
    dots: {
      source: "static",
      value: true,
    },
    loop: {
      source: "static",
      value: false,
    },
    productsPerPage: {
      source: "static",
      value: 4,
    },
  },
});
