import BaseListingPaginationPlugin from "src/plugin/listing/listing-pagination.plugin";

/**
 * This override sorting plugin.
 */
export default class ListingPaginationPlugin extends BaseListingPaginationPlugin {
    isListingPluginEnabled() {
        return this.listing.infinityScrollEnabled;
    }

    //Let's register our event for handling page changes.
    _registerButtonEvents() {
        this.buttons.forEach((radio) => {
            radio.addEventListener('change', this.onChangePage.bind(this));
        });
    }

    //We want to force scroll when user clicks on link.
    onChangePage(event) {
        if(!this.isListingPluginEnabled())
        {
            super.onChangePage(event);
            return;
        }

        //We just pass different params rest is unchanged.
        this.tempValue = event.target.value;
        this.listing.changeListing(true, {}, "pagination");
        this.tempValue = null;
    }
}