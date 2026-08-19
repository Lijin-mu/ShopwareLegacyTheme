import BaseListingPlugin from "src/plugin/listing/listing.plugin";

export default class ListingPlugin extends BaseListingPlugin {
    static options = {
        ...BaseListingPlugin.options,
        jsListingWrapperSelector: '.cms-element-product-listing .js-listing-wrapper',
        cmsProductPagesSelector: '.cms-element-product-listing .js-listing-wrapper .cms-listing-row',
        jsLoadMoreClassName: 'js-listing-load-next',
        jsLoadMoreButtonContainerClassName: 'btn-infinity-scroll-load',
        pageSize: 24
    };

    init() {
        super.init();

        this.lastObserverPageLoaded = Date.now();

        this.infinityScrollEnabled = this.el.hasAttribute('data-infinity-scroll');
        if (!this.infinityScrollEnabled){
            return;
        }
        
        this.initConfig();
        this.registerObserver();
        this.initLoadedPages();
        this.initParentOverflow();
        this.domParser = new DOMParser();
        this.lastPageLoadedByLoadMore = null;
        this._isEndReached = () => false;

        const urlMatch = window.location.href.match(/p=(\d+)/);
        if (urlMatch) {
            const loadedPage = parseInt(urlMatch[1]);
            for (let i = 1; i < loadedPage; i++) {
                this.changeListing(true, {p: i}, "observer");
            }
        }
    }

    initParentOverflow() {
        if (!this.config.stickyFooter) return;
        this.el.closest(".cms-block").style.overflow = "visible";
        this.el.closest(".cms-section").style.overflow = "visible";
    }

    correctPageIndexWithThreshold(page) {
        return this.config.maxPagesThreshold === 0 ? page : Math.min(page, this.config.maxPagesThreshold);
    }

    initConfig() {
        this.config = JSON.parse(this.el.getAttribute('data-infinity-scroll-options'));

        if (!this.config.renderPagination) {
            this.config.addNewPages = false;
            this.config.markLoadedPagesActive = false;
            this.el.querySelectorAll('ul.pagination').forEach(p => p.classList.add('d-none'));
        }

        if (!("allowLoadingPastThreshold" in this.config)) this.config.allowLoadingPastThreshold = false;
        if (this.config.maxPagesThreshold === 0) this.config.allowLoadingPastThreshold = false;
        if (!("scrollToTopOfPage" in this.config)) this.config.scrollToTopOfPage = false;
    }

    registerObserver() {
        this.observer = new IntersectionObserver(this.onIntersectPage.bind(this), {});
        this.observer.observe(this.el.querySelector('.' + this.options.jsLoadMoreClassName));
        this.enableObserverAfterMs(this.config.timeoutForObserver);
    }

    enableObserverAfterMs(ms) {
        this.observerEnabled = false;
        setTimeout(() => this.observerEnabled = true, ms);
    }

    onIntersectPage(entries) {
        if (!this.observerEnabled || Date.now() - this.lastObserverPageLoaded < 1000) return;

        entries.forEach(entry => {
            if (this._isEndReached && this._isEndReached()) {
                this.observer.disconnect();
                return;
            }
            entry.target.setAttribute('data-disabled', "false");
            const page = parseInt(entry.target.getAttribute('data-page'), 10);
            const disabled = entry.target.getAttribute('data-disabled') === "true";
            const isTriggeredByLoadMore = entry.target.classList.contains(this.options.jsLoadMoreClassName);
            
            if (!isNaN(page) && !disabled) {
                if (isTriggeredByLoadMore) {
                    if (this.lastPageLoadedByLoadMore === page) return;
                    this.lastPageLoadedByLoadMore = page;
                }
                this.changeListing(true, {p: page}, "observer");
                this.lastObserverPageLoaded = Date.now();
            }

            if (!isTriggeredByLoadMore) this.observer.unobserve(entry.target);
        });
    }

    updateNextPage() {
        const paginationEl = this.el.querySelector('[data-listing-pagination-options]');
        let total = 0, limit = 0;
        
        try {
            if (paginationEl) {
                const data = JSON.parse(paginationEl.getAttribute('data-listing-pagination-options'));
                total = parseInt(data.total || 0, 10);
                limit = parseInt(data.limit || 0, 10);
            }
        } catch (e) {
            console.error('[InfiniteScroll] Error parsing pagination data:', e);
        }

        let nextPage = Math.max(...this.loadedPages) + 1;
        if (total && limit) {
            const maxPages = Math.ceil(total / limit);
            if (nextPage > maxPages) nextPage = NaN;
        }

        if (!isNaN(nextPage) && nextPage !== this.correctPageIndexWithThreshold(nextPage) && !this.config.allowLoadingPastThreshold) {
            nextPage = NaN;
        }

        const el = this.el.querySelector('.' + this.options.jsLoadMoreClassName);
        if (el) el.setAttribute('data-page', nextPage);
        
        if (isNaN(nextPage)) {
            this._isEndReached = () => true;
            if (this.observer) this.observer.disconnect();
            this.showNoMoreProductsMessage();
        }
    }

    registerLoadNextPageButton() {
        const button = this.el.querySelector('.' + this.options.jsLoadMoreButtonContainerClassName);
        if (button) button.addEventListener("click", this._onNextPageLoadedByButton.bind(this));
    }

    _onNextPageLoadedByButton() {
        const loadNextPageEl = this.el.querySelector('.' + this.options.jsLoadMoreClassName);
        const page = parseInt(loadNextPageEl.getAttribute('data-page'), 10);
        this.changeListing(true, {p: page}, "button");
    }

    updateTempPageContents() {
        const firstChild = this.el.querySelector(`${this.buildPageSelector(this.loadedPages[0])} > *:first-child`);
        this.tempPageContents = firstChild ? firstChild.outerHTML.repeat(this.options.pageSize) : 
            "<div class='page-loader-dummy-div text-center vh-100 py-5'><div class='spinner-border spinner-border-lg' role='status' style='width: 3rem; height: 3rem;'><span class='sr-only'></span></div></div>";
    }

    initLoadedPages() {
        const loadedPage = this.el.querySelector('.cms-listing-row');
        const page = parseInt(loadedPage.getAttribute('data-page'), 10);

        this.loadedPages = [page];
        this.createdPages = [page];
        this.updateNextPage();
        this.updateTempPageContents();

        for (let i = 1; i < page; i++) {
            this.addEmptyPage(i);
            this.addLoadingElementLoaderClass(i);
        }

        if (page !== 1) setTimeout(this.scrollToPage.bind(this, page), this.config.timeoutForScroll);
    }

    _buildLabels() {
        super._buildLabels();
    }

    resetAllFilter() {
        this._removeAllLoaders();
        this._registry.forEach(filterPlugin => filterPlugin.resetAll());

        if (!this.infinityScrollEnabled) {
            this._buildRequest();
            this._buildLabels();
            return;
        }

        this.resetPages();
        this._buildRequest(true, { p: 1 }, false);
        if (this._filterPanelActive) this._buildLabels();
    }

    _removeAllLoaders() {
        if (this._filterPanelActive) this.removeLoadingIndicatorClass();
        if (this._cmsProductListingWrapperActive) {
            this.el.querySelectorAll(this.options.cmsProductPagesSelector).forEach(pageEl => {
                pageEl.classList.remove(this.options.loadingElementLoaderClass);
            });
        }
    }

    resetPages() {
        this.el.querySelectorAll(this.options.cmsProductPagesSelector).forEach(el => el.remove());
        this.deleteAllPagination();
        this.loadedPages = [];
        this.createdPages = [];
        this._removeAllLoaders();
        this.addEmptyPage(1);
        this.updateNextPage();

        const loadMoreEl = this.el.querySelector('.' + this.options.jsLoadMoreClassName);
        if (loadMoreEl) {
            this.observer.disconnect();
            this.observer.observe(loadMoreEl);
        }
        this.lastPageLoadedByLoadMore = null;
        this.registerLoadNextPageButton();
        this._isEndReached = () => false;
    }

    addEmptyPage(page, addToObserver = true) {
        if ((page > this.config.maxPagesThreshold && this.config.maxPagesThreshold !== 0 && !this.config.allowLoadingPastThreshold) || 
            this.createdPages.includes(page)) return;

        const loader = "<div class='page-loader-dummy-div text-center vh-100 py-5'><div class='spinner-border spinner-border-lg' role='status' style='width: 3rem; height: 3rem;'><span class='sr-only'></span></div></div>";
        const htmlToAdd = `<div class="row cms-listing-row" data-page="${page}">${loader}</div>`;

        if (page !== 1) {
            this.retrievePageHandle(page - 1).insertAdjacentHTML('afterend', htmlToAdd);
        } else {
            const el = this.el.querySelector(this.options.jsListingWrapperSelector);
            el.innerHTML = htmlToAdd + el.innerHTML;
        }
        if (addToObserver) this.observer.observe(this.retrievePageHandle(page));
        this.addPaginationButtonForPage(page);
        this.createdPages.push(page);
    }

    setPaginationContent(content) {
        this.el.querySelectorAll(`ul.pagination`).forEach(el => el.innerHTML = content);
    }

    removePaginationButtons() {
        this.el.querySelectorAll(`ul.pagination .page-item[data-page]`).forEach(el => el.remove());
    }

    addPaginationButtonForPage(page) {
        if ((page > this.config.maxPagesThreshold && this.config.maxPagesThreshold !== 0 && !this.config.allowLoadingPastThreshold) ||
            this.el.querySelector(`ul.pagination .page-item[data-page="${page}"]`)) return;

        const html = `<li class="page-item ${this.config.addNewPages ? "" : "d-none"}" data-page="${page}">
            <input type="radio" name="p" id="p${page}" value="${page}" class="d-none" title="pagination">
            <label class="page-link" for="p${page}">${page}</label>
        </li>`;

        this.el.querySelectorAll('ul.pagination:not(:empty)').forEach(pagination => {
            if (!pagination) return;
            if (page === 1) {
                const prevPage = pagination.querySelector('.page-item.page-prev');
                prevPage ? prevPage.insertAdjacentHTML('afterend', html) : pagination.innerHTML = html;
                return;
            }
            const pageBefore = pagination.querySelector(`.page-item[data-page="${page - 1}"]`);
            if (pageBefore) {
                pageBefore.insertAdjacentHTML('afterend', html);
                return;
            }
            const pageAfter = pagination.querySelector(`.page-item[data-page="${page + 1}"]`);
            if (pageAfter) pageAfter.insertAdjacentHTML('beforebegin', html);
        });
    }

    retrievePageHandle(page) {
        return this.el.querySelector(this.buildPageSelector(page));
    }

    buildPageSelector(page) {
        return this.options.cmsProductPagesSelector + `[data-page="${page}"]`;
    }

    changeListing(pushHistory = true, overrideParams = {}, source = "filter") {
        if (!this.infinityScrollEnabled) {
            super.changeListing(pushHistory, overrideParams);
            return;
        }
        
        const config = {
            filter: { resetNeeded: true, forceScroll: this.config.scrollToFirstPageAfterFilterUpdate },
            pagination: { resetNeeded: false, forceScroll: true },
            sorting: { resetNeeded: true, forceScroll: this.config.scrollToFirstPageAfterOrderUpdate },
            button: { resetNeeded: false, forceScroll: false },
            observer: { resetNeeded: false, forceScroll: false }
        };
        
        const { resetNeeded, forceScroll } = config[source] || config.filter;
        this._lastChangeSource = source;
        
        // Reset end reached flag when filters change to allow new requests
        if (source === "filter" || source === "sorting") {
            this._isEndReached = () => false;
        }
        
        if (resetNeeded) this.resetPages();
        this._buildRequest(pushHistory, overrideParams, forceScroll);
        if (this._filterPanelActive) this._buildLabels();
    }

    _buildRequest(pushHistory = true, overrideParams = {}, forceScroll = false) {
        if (!this.infinityScrollEnabled) {
            super._buildRequest(pushHistory, overrideParams);
            return;
        }

        const isFilterOrSorting = this._lastChangeSource === 'filter' || this._lastChangeSource === 'sorting';
        if (isFilterOrSorting) {
            this._isEndReached = () => false;
        }

        // Stop if we've reached the end (no more pages) - but allow if explicitly reset
        if (this._isEndReached && typeof this._isEndReached === 'function' && this._isEndReached()) {
            return;
        }

        const filters = this._fetchValuesOfRegisteredFilters();
        const mapped = this._mapFilters(filters);

        if (this._filterPanelActive) {
            this._showResetAll = !!Object.keys(mapped).length;
        }

        if (this.options.params) Object.assign(mapped, this.options.params);
        Object.assign(mapped, overrideParams);
        if (!mapped['p']) mapped['p'] = 1;

        let query = new URLSearchParams(mapped).toString();
        this.sendDataRequest(query, mapped['p'] || 1, forceScroll);

        ['slots', 'no-aggregations', 'reduce-aggregations', 'only-aggregations'].forEach(k => delete mapped[k]);
        query = new URLSearchParams(mapped).toString();

        if (pushHistory) {
            this._updateHistory(query);
        }
    }

    sendDataRequest(filterParams, page, scrollToPage) {
        if (!this.infinityScrollEnabled) {
            super.sendDataRequest(filterParams);
            return;
        }

        // Stop if we've reached the end (no more pages)
        if (this._isEndReached && this._isEndReached()) {
            return;
        }

        const maxCreatedPage = this.createdPages.length > 0 ? Math.max(...this.createdPages) : 0;
        const pagesAddedInThisRequest = [];
        for (let i = maxCreatedPage + 1; i <= page; i++) {
            this.addEmptyPage(i, i !== page);
            this.addLoadingElementLoaderClass(i);
            pagesAddedInThisRequest.push(i);
        }
        if (scrollToPage)
            this.scrollToPage(page);

        if (this._filterPanelActive) {
            this.addLoadingIndicatorClass();
        }

        if (this.options.disableEmptyFilter) {
            this.sendDisabledFiltersRequest();
        }
        
        
        this.httpClient.get(`${this.options.dataUrl}?${filterParams}`, (response, request) => {
            if (this._handleRequestError(response, request, pagesAddedInThisRequest)) return;
            
            this.renderResponse(response, page);
            if (this._filterPanelActive) this.removeLoadingIndicatorClass();
            if (this._cmsProductListingWrapperActive) this.removeLoadingElementLoaderClass(page);
            this._updateProductCount(response);
        });
        
    }

    _handleRequestError(response, request, pagesAddedInThisRequest) {
        if (request && request.status !== 0 && request.status >= 400) {
            if (request.status === 403 && this.options.dataUrl && this.options.dataUrl.includes('wishlist')) {
                this._cleanupFailedRequest(pagesAddedInThisRequest);
                return true;
            }
            console.warn(`Listing request failed with status ${request.status}`);
            this._cleanupFailedRequest(pagesAddedInThisRequest);
            return true;
        }
        
        if (response instanceof Error || typeof response !== 'string') {
            console.error('Listing request failed:', response);
            this._cleanupFailedRequest(pagesAddedInThisRequest);
            return true;
        }
        return false;
    }

    _cleanupFailedRequest(pagesAddedInThisRequest) {
        if (this._filterPanelActive) this.removeLoadingIndicatorClass();
        pagesAddedInThisRequest.forEach(pageNum => {
            this.removeLoadingElementLoaderClass(pageNum);
            const pageEl = this.retrievePageHandle(pageNum);
            if (pageEl) {
                pageEl.remove();
                const index = this.createdPages.indexOf(pageNum);
                if (index > -1) this.createdPages.splice(index, 1);
            }
        });
    }

    renderResponse(response) {
        if (!this.infinityScrollEnabled) {
            super.renderResponse(response);
            // Update counter immediately after response is rendered
            this._updateProductCount(response);
            return;
        }

        const parsedResponse = this.domParser.parseFromString(response, "text/html");
        const parsedListing = parsedResponse.querySelector(this.options.cmsProductPagesSelector);
        
        if (!parsedListing) {
            const redirectUrl = this._extractRedirectUrl(parsedResponse, response);
            const loginForm = parsedResponse.querySelector('form[action*="login"], form[action*="account"]');
            const errorPage = parsedResponse.querySelector('.alert-danger, .error-page, [class*="error"]');
            
            if (redirectUrl && (loginForm || errorPage || redirectUrl.includes('login') || redirectUrl.includes('account'))) {
                window.location.href = redirectUrl.startsWith('http') ? redirectUrl : 
                    (redirectUrl.startsWith('/') ? window.location.origin + redirectUrl : window.location.origin + '/' + redirectUrl);
            }
            
            this._removeAllLoaders();
            this.$emitter.publish('Listing/afterRenderResponse', {response});
            return;
        }
        
        const page = parseInt(parsedListing.getAttribute('data-page'), 10);
        const total = parseInt(parsedListing.getAttribute('data-total'), 10);
        const limit = parseInt(parsedListing.getAttribute('data-limit'), 10);
        const productCount = parseInt(parsedListing.getAttribute('data-product-count'), 10);
        
        this._updatePaginationData(page, total, limit);
        if (!isNaN(total) && this._filterPanel) {
            const countEl = this._filterPanel.querySelector('.product-listing-count');
            if (countEl) countEl.textContent = total.toString();
        }
        
        const parsedPagination = parsedResponse.querySelector(this.options.cmsProductListingWrapperSelector + ' .pagination');
        if (parsedPagination) {
            this.fillEmptyPaginations(parsedPagination.innerHTML);
            this.addMissingPaginationButtons(parsedPagination);
        }

        const pageEl = this.retrievePageHandle(page);
        if (!pageEl) return;

        this.observer.unobserve(pageEl);
        pageEl.innerHTML = parsedListing.innerHTML;
        this.markPaginationActive(page);
        if (!this.loadedPages.includes(page)) this.loadedPages.push(page);
        
        const isLastPage = page >= Math.ceil(total / limit);
        if (productCount === 0 || isLastPage) {
            this._isEndReached = () => true;
            this.observer.disconnect();
            if (productCount === 0) pageEl.remove();
            this.showNoMoreProductsMessage();
            this.$emitter.publish('Listing/afterRenderResponse', {response});
            return;
        }

        this.updateNextPage();
        this._registry.forEach(item => {
            if (typeof item.afterContentChange === 'function') item.afterContentChange();
        });
        window.PluginManager.initializePlugins();
        this._updateProductCount(response);
        this.$emitter.publish('Listing/afterRenderResponse', {response});
    }

    _extractRedirectUrl(parsedResponse, response) {
        const metaRefresh = parsedResponse.querySelector('meta[http-equiv="refresh"]');
        if (metaRefresh?.content) {
            const urlMatch = metaRefresh.content.match(/url=([^;]+)/i);
            if (urlMatch?.[1]) return urlMatch[1].trim();
        }
        const loginLink = parsedResponse.querySelector('a[href*="login"], a[href*="account"]');
        if (loginLink?.href) return loginLink.href;
        const redirectMatch = response.match(/(?:window\.)?location\.href\s*=\s*['"]([^'"]+)['"]/);
        return redirectMatch?.[1] || null;
    }

    _updatePaginationData(page, total, limit) {
        const paginationEl = this.el.querySelector('[data-listing-pagination-options]');
        if (paginationEl && !isNaN(total)) {
            try {
                const data = JSON.parse(paginationEl.getAttribute('data-listing-pagination-options'));
                Object.assign(data, { total, page, limit });
                paginationEl.setAttribute('data-listing-pagination-options', JSON.stringify(data));
            } catch (e) {}
        }
    }

    _updateProductCount(response) {
        try {
            if (!response || typeof response !== 'string') return;
            const parsedResponse = this.domParser.parseFromString(response, 'text/html');
            
            let newTotal = parsedResponse.querySelector(this.options.cmsProductPagesSelector)?.getAttribute('data-total') ||
                parsedResponse.querySelector('.loaded-data-total')?.getAttribute('data-total') ||
                parsedResponse.querySelector('.product-listing-count')?.textContent.trim() ||
                null;

            if (!newTotal) {
                const paginationEl = this.el.querySelector('[data-listing-pagination-options]');
                if (paginationEl) {
                    try {
                        const data = JSON.parse(paginationEl.getAttribute('data-listing-pagination-options'));
                        if (data?.total) newTotal = data.total.toString();
                    } catch (e) {}
                }
            }

            if (newTotal) {
                ['.product-listing-count', '.filter--count'].forEach(selector => {
                    if (this._filterPanel) {
                        const el = this._filterPanel.querySelector(selector);
                        if (el) el.textContent = newTotal;
                    }
                    document.querySelectorAll(selector).forEach(el => {
                        if (el.textContent !== newTotal) el.textContent = newTotal;
                    });
                });
            }
        } catch (e) {
            console.error('Error updating product count:', e);
        }
    }

    markPaginationActive(page) {
        if (!this.config.markLoadedPagesActive) return;
        this.el.querySelectorAll(`.pagination .page-item[data-page="${page}"]`).forEach(el => el.classList.add('active'));
    }

    unmarkPaginationActive(page) {
        this.el.querySelectorAll(`.pagination .page-item[data-page="${page}"]`).forEach(el => el.classList.remove('active'));
    }

    fillEmptyPaginations(content) {
        this.el.querySelectorAll(`ul.pagination:empty`).forEach(el => el.innerHTML = content);
    }

    addMissingPaginationButtons(pagination) {
        const pagesToAdd = [...new Set([...pagination.querySelectorAll('.page-item[data-page]')].map(el => el.getAttribute('data-page')))];
        pagesToAdd.sort((a, b) => a - b).forEach(page => this.addPaginationButtonForPage(page));
    }

    deleteAllPagination() {
        this.el.querySelectorAll(`.pagination`).forEach(el => el.innerHTML = "");
    }

    addLoadingElementLoaderClass(page) {
        if (!this.infinityScrollEnabled) {
            super.addLoadingElementLoaderClass();
            return;
        }
        const pageEl = this.retrievePageHandle(page);
        if (pageEl) pageEl.classList.add(this.options.loadingElementLoaderClass);
    }

    removeLoadingElementLoaderClass(page) {
        if (!this.infinityScrollEnabled) {
            super.removeLoadingElementLoaderClass();
            return;
        }
        const pageEl = this.retrievePageHandle(page);
        if (pageEl) pageEl.classList.remove(this.options.loadingElementLoaderClass);
    }

    scrollToPage(page) {
        this.enableObserverAfterMs(500);
        if (page === 1 && this.config.scrollToTopOfPage) {
            window.scrollTo({top: 0, behavior: "instant"});
            return;
        }
        this.retrievePageHandle(page);
    }

    sendDisabledFiltersRequest() {
        const filters = this._fetchValuesOfRegisteredFilters();
        const mapped = this._mapFilters(filters);
        if (this.options.params) Object.assign(mapped, this.options.params);
        this._allFiltersInitializedDebounce = () => {};

        const filterParams = this._getDisabledFiltersParamsFromParams(mapped);
        this.httpClient.get(`${this.options.filterUrl}?${new URLSearchParams(filterParams).toString()}`, (response) => {
            const filter = JSON.parse(response);
            const count = parseInt(filter.total_product_count?.count) + parseInt(filter.total_variant_product_count?.buckets?.length);
            document.querySelectorAll('.filter-product-count').forEach(el => el.innerHTML = count);
            this._registry.forEach(item => {
                if (typeof item.refreshDisabledState === 'function') item.refreshDisabledState(filter, filterParams);
            });
        });
    }

    showNoMoreProductsMessage() {
        if (this.el.querySelector('.infinite-scroll-end-message')) return;
        const loadMoreEl = this.el.querySelector('.' + this.options.jsLoadMoreClassName);
        if (loadMoreEl) loadMoreEl.innerHTML = `<div class="infinite-scroll-end-message></div>`;
    }

}