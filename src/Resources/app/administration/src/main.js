import enGBSnippets from './snippet/en-GB.json';
import deDeSnippets from './snippet/de-DE.json';

Shopware.Locale.extend('en-GB', enGBSnippets);
Shopware.Locale.extend('de-DE', deDeSnippets);

// Import elements before blocks to ensure proper initialization
import './module/sw-cms/elements/enquiry-form';
import './module/sw-cms/blocks/enquiry-form';

import './module/sw-cms/elements/product-return-form';
import './module/sw-cms/blocks/product-return-form';

import './module/sw-cms/elements/recently-viewed-product-slider';
import './module/sw-cms/blocks/recently-viewed-product-slider';

import './module/sw-cms/elements/cta';
import './module/sw-cms/blocks/hero-slider';
import './module/sw-cms/blocks/column-cta';

import './module/sw-cms/elements/product-list-cta';
import './module/sw-cms/blocks/product-list';



