import Plugin from 'src/plugin-system/plugin.class';
/**
 * This override sorting plugin.
 */
export default class CustomListingSortingPlugin extends Plugin {

    init() {
        this.select = this.el.querySelector('select');
        this._registerEvents();
    }

      /**
     * @private
     */
    _registerEvents() {
        this.select.addEventListener('change', (event) => {
            let selectedValue = event.target.value;
            this.listingSelectContainer = document.querySelector('[data-listing-sorting="true"]');
            let listingSelect = this.listingSelectContainer.querySelector('select');
            listingSelect.value = selectedValue;
            let dispatchEvent = new Event('change');
            listingSelect.dispatchEvent(dispatchEvent);
            console.log('Sorting changed to:', selectedValue);
        });
    }
}
