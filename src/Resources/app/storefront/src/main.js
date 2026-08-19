PluginManager.register('MenuScrollerPlugin', () => import('./plugins/scroll-menu.plugin'), '[data-navbar=true]');
PluginManager.register('FilterPanelTogglePlugin', () => import('./plugins/filter-toggle.plugin'), '.cms-element-sidebar-filter');
PluginManager.register('EnquiryFormPlugin', () => import('./plugins/enquiry-form.plugin'), '[data-enquiry-form]');
PluginManager.register('ProductReturnFormPlugin', () => import('./plugins/product-return-form.plugin'), '[data-product-return-form]');
PluginManager.register('NewsletterFormPlugin', () => import('./plugins/newsletter-form.plugin'), '[data-newsletter-form]');

PluginManager.override('Listing', () => import('./plugins/listing.plugin'), '[data-listing]');
PluginManager.override('ListingSorting', () => import("./plugins/listing-sorting.plugin"), '[data-listing-sorting]');
PluginManager.override('ListingPagination', () => import("./plugins/listing-pagination.plugin"), '[data-listing-pagination]');
PluginManager.override('ProductSlider', () => import('./plugins/product-slider.plugin'), '[data-product-slider]');
PluginManager.register('CustomListingSortingPlugin', () => import('./plugins/custom-listing-sorting.plugin'), '[data-filter-panel-sorting=true]');
PluginManager.register('ReviewTabScrollPlugin', () => import('./plugins/review-tab-scroll.plugin'), '[data-show-tab]');

PluginManager.register('ProductViewed', () => import('./plugins/product-viewed.plugin'), '[data-product-viewed]');
PluginManager.register('RecentlyViewedProduct', () => import('./plugins/recently-viewed-product.plugin'), '[data-recently-viewed-product]');
PluginManager.register('SidebarActiveMenuPlugin', () => import('./plugins/sidebar-active-menu.plugin'), '.cms-section-sidebar-sidebar-content');