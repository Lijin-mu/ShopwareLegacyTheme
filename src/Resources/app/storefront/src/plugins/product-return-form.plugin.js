import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class ProductReturnFormPlugin extends Plugin {
    static options = {
        formSelector: '[data-product-return-form-submit]',
        submitButtonSelector: '.product-return-form-submit',
        successAlertSelector: '.product-return-form-success-alert',
        errorAlertSelector: '.product-return-form-error-alert',
        submitUrl: '/product-return-form/submit'
    };

    init() {
        this.httpClient = new HttpClient();
        this.form = this.el.querySelector(this.options.formSelector);
        this.submitButton = this.el.querySelector(this.options.submitButtonSelector);
        this.successAlert = this.el.querySelector(this.options.successAlertSelector);
        this.errorAlert = this.el.querySelector(this.options.errorAlertSelector);

        if (!this.form) {
            return;
        }

        this._registerEvents();
        this._focusDescriptionIfPreFilled();
    }

    _registerEvents() {
        this.form.addEventListener('submit', this._onSubmit.bind(this));
    }

    _focusDescriptionIfPreFilled() {
        const commentField = this.form.querySelector('[name="comment"]');
        const presetValue = commentField?.value?.trim();

        if (commentField && presetValue) {
            setTimeout(() => {
                commentField.focus();
                const textLength = commentField.value.length;
                commentField.setSelectionRange(textLength, textLength);
            }, 100);
        }
    }

    _onSubmit(event) {
        event.preventDefault();
        this._clearErrors();
        this._hideAlerts();

        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData.entries());

        if (!this._validateForm(data)) {
            return;
        }

        this._setLoadingState(true);

        this.httpClient.post(
            this.options.submitUrl,
            JSON.stringify(data),
            (response) => this._onSuccess(response),
            'application/json',
            false,
            (error) => this._onError(error)
        );
    }

    _validateForm(data) {
        let isValid = true;

        if (!data.customerNumber || data.customerNumber.trim().length < 1) {
            this._showFieldError('customerNumber', this._translate('customerNumber'));
            isValid = false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!data.email || !emailRegex.test(data.email)) {
            this._showFieldError('email', this._translate('email'));
            isValid = false;
        }

        if (!data.invoiceNumber || data.invoiceNumber.trim().length < 1) {
            this._showFieldError('invoiceNumber', this._translate('invoiceNumber'));
            isValid = false;
        }

        if (!data.itemNumbers || data.itemNumbers.trim().length < 1) {
            this._showFieldError('itemNumbers', this._translate('itemNumbers'));
            isValid = false;
        }

        const privacyCheck = this.form.querySelector('[name="privacyCheck"]');
        if (!privacyCheck || !privacyCheck.checked) {
            this._showFieldError('privacyCheck', this._translate('privacyCheck'));
            isValid = false;
        }

        return isValid;
    }

    _onSuccess(response) {
        this._setLoadingState(false);

        try {
            const data = JSON.parse(response);

            if (data.success) {
                this._showSuccessAlert();
                this.form.reset();
                this.successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                this._handleServerErrors(data.errors || {});
            }
        } catch (error) {
            this._showErrorAlert();
        }
    }

    _onError(error) {
        this._setLoadingState(false);

        try {
            const data = JSON.parse(error);
            if (data.errors) {
                this._handleServerErrors(data.errors);
            } else {
                this._showErrorAlert();
            }
        } catch (e) {
            this._showErrorAlert();
        }
    }

    _handleServerErrors(errors) {
        Object.keys(errors).forEach((field) => {
            this._showFieldError(field, errors[field]);
        });

        this._showErrorAlert();
    }

    _showFieldError(fieldName, message) {
        const input = this.form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.add('is-invalid');
            let feedback = input.parentElement.querySelector('.invalid-feedback');
            
            // For checkboxes, the feedback might be in a different location
            if (!feedback && input.type === 'checkbox') {
                feedback = input.closest('.form-check')?.querySelector('.invalid-feedback');
            }
            
            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = 'block';
            }
        }
    }

    _clearErrors() {
        const invalidInputs = this.form.querySelectorAll('.is-invalid');
        invalidInputs.forEach((input) => {
            input.classList.remove('is-invalid');
        });

        const feedbacks = this.form.querySelectorAll('.invalid-feedback');
        feedbacks.forEach((feedback) => {
            feedback.textContent = '';
            feedback.style.display = 'none';
        });
    }

    _showSuccessAlert() {
        if (this.successAlert) {
            this.successAlert.classList.remove('d-none');
        }
    }

    _showErrorAlert() {
        if (this.errorAlert) {
            this.errorAlert.classList.remove('d-none');
        }
    }

    _hideAlerts() {
        this.successAlert?.classList.add('d-none');
        this.errorAlert?.classList.add('d-none');
    }

    _setLoadingState(isLoading) {
        if (!this.submitButton) {
            return;
        }

        const buttonText = this.submitButton.querySelector('.button-text');
        const spinner = this.submitButton.querySelector('.spinner-border');

        if (isLoading) {
            this.submitButton.disabled = true;
            buttonText?.classList.add('d-none');
            spinner?.classList.remove('d-none');
        } else {
            this.submitButton.disabled = false;
            buttonText?.classList.remove('d-none');
            spinner?.classList.add('d-none');
        }
    }

    _translate(key) {
        return window?.legacyThemeReturnForm?.validation?.[key] || 'Please check this field.';
    }
}
