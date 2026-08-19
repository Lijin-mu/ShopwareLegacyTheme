import Plugin from 'src/plugin-system/plugin.class';

export default class FilterPanelTogglePlugin extends Plugin {
    init() {
        this.transitionDuration = 500; // in ms (matches CSS)
        this._registerEvents();
    }

    _registerEvents() {
        const toggleBtn = this.el.querySelector('.filter-panel-toggle-button');
        const filterCloseBtn = this.el.querySelector('.filter--btn-apply');
        const panel = this.el.querySelector('.filter-panel');
        const activeItemContainer = this.el.querySelector('.filter-panel-active-container');

        if (!toggleBtn || !panel) {
            return;
        }

        // Initial setup
        panel.style.overflow = 'hidden';
        panel.style.maxHeight = '0';
        panel.style.transition = 'max-height 0.5s ease';

        toggleBtn.addEventListener('click', () => {
            this._togglePanel(panel);
        });

        if(filterCloseBtn){
             filterCloseBtn.addEventListener('click', () => {
                this._togglePanel(panel);
            });
        }

        const observer = new MutationObserver(() => {
            // Update styles or toggle a class
            this._updatePanel(panel);
            console.log("change");
        });

        observer.observe(activeItemContainer, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true
        });
    }

    _updatePanel(panel) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
            this.el.classList.add('is-open');
            setTimeout(() => {
                panel.style.overflow = 'visible';
            }, this.transitionDuration);
    }

    _togglePanel(panel) {
        if (panel.style.maxHeight && panel.style.maxHeight !== '0px') {
            // Collapse
            panel.style.overflow = 'hidden';
            panel.style.maxHeight = '0';
            this.el.classList.remove('is-open');
        } else {
            // Expand (use scrollHeight for dynamic height)
            panel.style.maxHeight = panel.scrollHeight + 'px';
            this.el.classList.add('is-open');
             // After transition → unlock overflow for natural height
            setTimeout(() => {
                panel.style.overflow = 'visible';
            }, this.transitionDuration);
        }
    }
}
