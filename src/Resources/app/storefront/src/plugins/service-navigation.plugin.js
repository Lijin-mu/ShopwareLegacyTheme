import Plugin from 'src/plugin-system/plugin.class';

export default class ServiceNavigationPlugin extends Plugin {
    static options = {
        serviceDropdownSelector: '[data-service-dropdown]',
        serviceDropdownClass: 'service-dropdown',
        activeClass: 'active'
    };

    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.serviceDropdown = this.el.querySelector('.service-dropdown');
        const serviceLink = this.el.querySelector('.service-link');
        
        if (serviceLink && this.serviceDropdown) {
            serviceLink.addEventListener('click', this._onServiceLinkClick.bind(this));
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', this._onDocumentClick.bind(this));
    }

    _onServiceLinkClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        this.serviceDropdown.classList.toggle(this.options.activeClass);
    }

    _onDocumentClick(event) {
        if (!this.el.contains(event.target)) {
            this.serviceDropdown.classList.remove(this.options.activeClass);
        }
    }
}
