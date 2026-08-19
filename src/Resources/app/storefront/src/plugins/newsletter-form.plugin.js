import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class NewsletterFormPlugin extends Plugin {
    static options = {
        responseContainerSelector: '[data-newsletter-response]',
        successClass: 'newsletter-response--success',
        errorClass: 'newsletter-response--error',
    };

    init() {
        this.httpClient = new HttpClient();
        this.responseContainer = this.el.querySelector(this.options.responseContainerSelector);
        this.submitButton = this.el.querySelector('button[type="submit"]');
        this.spinner = this.submitButton?.querySelector('[data-newsletter-spinner]');
        this.formHandlerInstance = this._resolveFormHandlerInstance();

        this.el.addEventListener('submit', this._onSubmit.bind(this));
    }

    _onSubmit(event) {
        event.preventDefault();

        if (typeof this.el.checkValidity === 'function' && !this.el.checkValidity()) {
            this._removeFormHandlerLoader();
            return;
        }

        this._setLoadingState(true);
        this._renderResponse('');

        const formData = new FormData(this.el);

        this.httpClient.post(
            this.el.action,
            formData,
            (responseText, request) => {
                this._setLoadingState(false);
                this._removeFormHandlerLoader();

                if (request.status >= 200 && request.status < 300) {
                    this._handleResponse(responseText);
                    return;
                }

                this._renderErrorMessage();
            }
        );
    }

    _handleResponse(responseText) {
        let alerts;

        try {
            alerts = JSON.parse(responseText);
        } catch (error) {
            this._renderErrorMessage();
            return;
        }

        if (!Array.isArray(alerts) || alerts.length === 0) {
            this._renderErrorMessage();
            return;
        }

        const hasDanger = alerts.some((alert) => alert.type === 'danger');
        const markup = alerts.map((alert) => alert.alert).join('');

        this._renderResponse(markup, hasDanger ? 'error' : 'success');

        if (!hasDanger) {
            this.el.reset();
            this._scrollResponseIntoView();
        }
    }

    _renderResponse(markup = '', state = null) {
        if (!this.responseContainer) {
            return;
        }

        this.responseContainer.innerHTML = markup;

        this.responseContainer.classList.remove(
            this.options.successClass,
            this.options.errorClass
        );

        if (state === 'success') {
            this.responseContainer.classList.add(this.options.successClass);
        }

        if (state === 'error') {
            this.responseContainer.classList.add(this.options.errorClass);
        }
    }

    _renderErrorMessage() {
        const fallback = `
            <div class="alert alert-danger" role="alert">
                ${this._translate('genericError')}
            </div>
        `;

        this._renderResponse(fallback, 'error');
    }

    _scrollResponseIntoView() {
        if (!this.responseContainer) {
            return;
        }

        this.responseContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    _setLoadingState(isLoading) {
        if (!this.submitButton) {
            return;
        }

        this.submitButton.disabled = isLoading;

        if (this.spinner) {
            this.spinner.classList.toggle('d-none', !isLoading);
        }
    }

    _translate(key) {
        const dictionary = window.legacyTheme?.newsletterMessages || {};

        return dictionary[key] || 'Something went wrong. Please try again.';
    }

    _removeFormHandlerLoader() {
        const instance = this.formHandlerInstance ?? this._resolveFormHandlerInstance();

        if (instance && typeof instance.removeLoadingIndicator === 'function') {
            instance.removeLoadingIndicator();
        }
    }

    _resolveFormHandlerInstance() {
        if (!window.PluginManager || typeof window.PluginManager.getPluginInstancesFromElement !== 'function') {
            return null;
        }

        const instances = window.PluginManager.getPluginInstancesFromElement(this.el, 'FormHandler');

        if (Array.isArray(instances) && instances.length > 0) {
            return instances[0];
        }

        return null;
    }
}


