import Plugin from "src/plugin-system/plugin.class";
import ElementReplaceHelper from "src/helper/element-replace.helper";
import SliderSettingsHelper from 'src/plugin/slider/helper/slider-settings.helper';
import HttpClient from "src/service/http-client.service";
import { tns } from 'tiny-slider/src/tiny-slider.module';

export default class RecentlyViewedProduct extends Plugin {

  init() {
    this._loadRecentlyViewedProductIds();

    this._client = new HttpClient();

    this._fetchProducts();
  }

  _correctIndexSettings() {
    super._correctIndexSettings();

    this.options.thumbnailSlider.startIndex -= 1;
    this.options.thumbnailSlider.startIndex = (this.options.thumbnailSlider.startIndex < 0) ? 0 : this.options.thumbnailSlider.startIndex;
  }

  _getSettings(viewport) {
    super._getSettings(viewport);

    this._thumbnailSliderSettings = SliderSettingsHelper.getViewportSettings(this.options.thumbnailSlider, viewport);
  }

  _initSlider() {

    const container = document.querySelector(this.options.containerSelector);
    const controlsContainer = document.querySelector(this.options.controlsWrapper);
    this.options.thumbnailSlider = SliderSettingsHelper.prepareBreakpointPxValues(this.options.sliderOptions);



    const onInitThumbnails = () => {
      this.$emitter.publish('initThumbnailSlider');
    };

    if (container) {
      this._slider = tns({
        container,
        controlsContainer,
        onInit: onInitThumbnails,
        ...this.options.thumbnailSlider,
      });
       const titleElement = document.querySelector(this.options.titleSelector);
        if(titleElement) {
          if (!Array.isArray(this._productIds) || this._productIds.length > 0) {
          titleElement.classList.add('show-title');
        } else {
          titleElement.classList.remove('show-title');
        } 
       }
      
    }

    this.$emitter.publish('afterInitSlider');
}

  /**
   * Load the recently viewed product IDs from local storage.
   *
   * @return {void}
   */
  _loadRecentlyViewedProductIds() {
    let productIds =
      JSON.parse(localStorage.getItem("ProductsViewed")) || [];
    productIds = productIds.reverse();
    this._productIds = productIds;
  }

  /**
   * Fetches the products by making a POST request to the server.
   *
   * @return {Promise} A promise that resolves with the response from the server.
   */
  _fetchProducts() {
    if (this.options.route)
      this._client.post(
        this.options.route,
        this._getData(),
        this._onResponse.bind(this)
      );
  }

  /**
   * Handles the response received from the server.
   *
   * @param {Object} response - The response received from the server.
   */
  _onResponse(response) {
    if (this.options.replaceSelectors) {
      ElementReplaceHelper.replaceFromMarkup(
        response,
        this.options.replaceSelectors,
        false
      );

      this._initSlider();
    }
  }

  _getData() {
    return JSON.stringify({
      productIds: this._productIds,
      layoutColumns: this.options.layoutColumns,
      sidebar: this.options.sidebar,
      boxLayout: this.options.boxLayout,
      displayMode: this.options.displayMode,
      productsPerPage: parseInt(this.options.productsPerPage),
    });
  }
}
