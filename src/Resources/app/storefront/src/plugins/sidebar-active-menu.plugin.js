import Plugin from 'src/plugin-system/plugin.class';
/**
 * This override sorting plugin.
 */
export default class SidebarActiveMenuPlugin extends Plugin {

    init() {
        this._textBlock = this.el.querySelectorAll('.cms-element-text');
        this._registerEvents();
    }

      /**
     * @private
     */
    _registerEvents() {
            const currentPath = window.location.pathname;
            this._textBlock.forEach(textBlock => {
                textBlock.querySelectorAll('a[href]').forEach(a => {
                    if (a.getAttribute('href') === currentPath) {
                        a.classList.add('active');
                    }
                });
            });
    }
}
