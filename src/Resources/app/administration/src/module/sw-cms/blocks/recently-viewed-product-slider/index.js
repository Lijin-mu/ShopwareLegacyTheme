import "./component";
import "./preview";

Shopware.Service("cmsService").registerCmsBlock({
  name: "recently-viewed-product-slider",
  label: "sw-cms.blocks.recently-viewed-product-slider.label",
  category: "commerce",
  component: "sw-cms-block-recently-viewed-product-slider",
  previewComponent: "sw-cms-preview-recently-viewed-product-slider",
  defaultConfig: {
    marginBottom: "20px",
    marginTop: "20px",
    marginLeft: "20px",
    marginRight: "20px",
    sizingMode: "boxed",
  },
  slots: {
    product: {
      type: "recently-viewed-product-slider",
      default: null,
    },
  },
});
