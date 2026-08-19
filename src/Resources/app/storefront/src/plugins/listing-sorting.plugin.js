import BaseListingSortingPlugin from "src/plugin/listing/listing-sorting.plugin";

/**
 * This override sorting plugin.
 */
export default class ListingSortingPlugin extends BaseListingSortingPlugin {

    init() {
        this.select = this.el.querySelector('select');
        this.selectItems = document.querySelectorAll('.sorting-label');
        this._registerEvents();
    }

      /**
     * @private
     */
      _registerEvents() {
        let radioSortOptions = document.querySelectorAll('.sorting-input');
        let sortingSelect = this.el.querySelector('select');

        const changeselectItem = () => {
            let selectItemLabels = document.querySelectorAll('.sorting-label');
            selectItemLabels.forEach(selectItem => {
                selectItem.classList.remove('selected');
            });
        };
       
        radioSortOptions.forEach(function(radioButton) {
        radioButton.addEventListener('change', function(){
            changeselectItem();
            sortingSelect.value = radioButton.value;
            const event = new Event('change');
            sortingSelect.dispatchEvent(event);
            let sortingLabel = document.querySelector('.sorting-text');
            sortingLabel.innerText = radioButton.nextElementSibling.innerText;
            radioButton.nextElementSibling.classList.add('selected');

        });
    });
    this.select.addEventListener('change', this.onChangeSorting.bind(this));
    }

    isListingPluginEnabled() {
        return this.listing.infinityScrollEnabled;
    }

    afterContentChange() {
        if(!this.isListingPluginEnabled()) {
            super.afterContentChange();
            return;
        }

        //The line which was here originally caused order select to not work after first time.
    }

    onChangeSorting(event) {
        // this._changeselectItem();
        if(!this.isListingPluginEnabled()) {
            super.onChangeSorting(event);
            return;
        }

        //Just same as parent but reset it and force first page.
        this.options.sorting = event.target.value;
        this.listing.changeListing(true, {p: 1}, "sorting");
    }
}
