import Plugin from 'src/plugin-system/plugin.class';

export default class FilterPanelTogglePlugin extends Plugin {
    init() {
        this.transitionDuration = 500;
        this._registerEvents();
    }

    _registerEvents() {
        this.toggleBtn = this.el.querySelector('.filter-panel-toggle-button');
        this.filterCloseBtn = this.el.querySelector('.filter--btn-apply');
        this.panel = this.el.querySelector('.filter-panel');

        if (!this.toggleBtn || !this.panel) {
            return;
        }

        this.panel.style.overflow = 'hidden';
        this.panel.style.maxHeight = '0px';

        this.toggleBtn.addEventListener('click', this._togglePanel.bind(this));

        if (this.filterCloseBtn) {
            this.filterCloseBtn.addEventListener('click', this._togglePanel.bind(this));
        }

        const activeItemContainer = this.el.querySelector('.filter-panel-active-container');
        if (activeItemContainer) {
            this._activeItemsObserver = new MutationObserver(() => {
                if (this.el.classList.contains('is-open')) {
                    this._setPanelHeight();
                }
            });
            this._activeItemsObserver.observe(activeItemContainer, {
                childList: true,
                subtree: true,
            });
        }
    }

    _setPanelHeight() {
        window.clearTimeout(this._overflowTimer);
        this.panel.style.overflow = 'hidden';

        window.requestAnimationFrame(() => {
            const current = this.panel.style.maxHeight;
            this.panel.style.maxHeight = 'none';
            const height = this.panel.scrollHeight;
            this.panel.style.maxHeight = current;
            this.panel.getBoundingClientRect();
            this.panel.style.maxHeight = `${height}px`;

            this._overflowTimer = window.setTimeout(() => {
                if (this.el.classList.contains('is-open')) {
                    this.panel.style.overflow = 'visible';
                }
            }, this.transitionDuration);
        });
    }

    _togglePanel() {
        if (this.el.classList.contains('is-open')) {
            this.panel.style.overflow = 'hidden';
            this.panel.style.maxHeight = '0px';
            this.el.classList.remove('is-open');
            return;
        }

        this.el.classList.add('is-open');
        this._setPanelHeight();
    }
}
