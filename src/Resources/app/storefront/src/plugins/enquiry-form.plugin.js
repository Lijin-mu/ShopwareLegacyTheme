import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class EnquiryFormPlugin extends Plugin {
    static options = {
        formSelector: '[data-enquiry-form-submit]',
        submitButtonSelector: '.enquiry-form-submit',
        successAlertSelector: '.enquiry-form-success-alert',
        errorAlertSelector: '.enquiry-form-error-alert',
        submitUrl: '/enquiry-form/submit'
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
        // If description field has product info pre-filled by Twig, focus it
        const descriptionField = this.form.querySelector('[name="description"]');
        const productNameField = this.form.querySelector('[name="productName"][data-product-name]');
        
        if (descriptionField && productNameField && descriptionField.value.trim()) {
            // Small delay to ensure page is fully loaded
            setTimeout(() => {
                descriptionField.focus();
                // Move cursor to end of text
                const textLength = descriptionField.value.length;
                descriptionField.setSelectionRange(textLength, textLength);
            }, 100);
        }
    }

    _onSubmit(event) {
        event.preventDefault();

        // Clear previous errors
        this._clearErrors();
        this._hideAlerts();

        // Get form data
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData.entries());

        // Validate client-side
        if (!this._validateForm(data)) {
            return;
        }

        // Show loading state
        this._setLoadingState(true);

        // Submit form via AJAX
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

        // Validate first name
        if (!data.firstName || data.firstName.trim().length < 2) {
            this._showFieldError('firstName', 'Please enter a valid first name (minimum 2 characters)');
            isValid = false;
        }

        // Validate last name
        if (!data.lastName || data.lastName.trim().length < 2) {
            this._showFieldError('lastName', 'Please enter a valid last name (minimum 2 characters)');
            isValid = false;
        }

        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!data.email || !emailRegex.test(data.email)) {
            this._showFieldError('email', 'Please enter a valid email address');
            isValid = false;
        }

        // Validate phone if provided
        if (data.phone && data.phone.trim().length > 0 && data.phone.trim().length < 5) {
            this._showFieldError('phone', 'Please enter a valid phone number');
            isValid = false;
        }

        return isValid;
    }

    _onSuccess(response) {
        this._setLoadingState(false);

        try {
            const data = JSON.parse(response);

            if (data.success) {
                // Show success message
                this._showSuccessAlert(data.message);
                
                // Reset form
                this.form.reset();

                // Scroll to success message
                this.successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                this._handleServerErrors(data.errors);
            }
        } catch (error) {
            this._showErrorAlert('An error occurred while processing your request.');
        }
    }

    _onError(error) {
        this._setLoadingState(false);

        try {
            const data = JSON.parse(error);
            
            if (data.errors) {
                this._handleServerErrors(data.errors);
            } else {
                this._showErrorAlert(data.message || 'An error occurred. Please try again.');
            }
        } catch (e) {
            this._showErrorAlert('An error occurred. Please try again.');
        }
    }

    _handleServerErrors(errors) {
        Object.keys(errors).forEach(field => {
            this._showFieldError(field, errors[field]);
        });

        // Show general error alert
        this._showErrorAlert('Please correct the errors in the form.');
    }

    _showFieldError(fieldName, message) {
        const input = this.form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = 'block';
            }
        }
    }

    _clearErrors() {
        // Remove all error states
        const invalidInputs = this.form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });

        // Clear all error messages
        const feedbacks = this.form.querySelectorAll('.invalid-feedback');
        feedbacks.forEach(feedback => {
            feedback.textContent = '';
            feedback.style.display = 'none';
        });
    }

    _showSuccessAlert(message) {
        if (this.successAlert) {
            // Just show the alert with the translated content from Twig
            // Don't replace the content to preserve translations
            this.successAlert.classList.remove('d-none');
        }
    }

    _showErrorAlert(message) {
        if (this.errorAlert) {
            // Just show the alert with the translated content from Twig
            // Don't replace the content to preserve translations
            this.errorAlert.classList.remove('d-none');
        }
    }

    _hideAlerts() {
        if (this.successAlert) {
            this.successAlert.classList.add('d-none');
        }
        if (this.errorAlert) {
            this.errorAlert.classList.add('d-none');
        }
    }

    _setLoadingState(isLoading) {
        const buttonText = this.submitButton.querySelector('.button-text');
        const spinner = this.submitButton.querySelector('.spinner-border');

        if (isLoading) {
            this.submitButton.disabled = true;
            if (buttonText) buttonText.classList.add('d-none');
            if (spinner) spinner.classList.remove('d-none');
        } else {
            this.submitButton.disabled = false;
            if (buttonText) buttonText.classList.remove('d-none');
            if (spinner) spinner.classList.add('d-none');
        }
    }
}

