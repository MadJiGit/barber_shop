/**
 * Form Validation Helper
 * Provides better validation messages and UX
 */

const FormValidator = {
    /**
     * Validation rules in Bulgarian
     */
    messages: {
        required: 'Това поле е задължително',
        email: 'Моля въведете валиден email адрес',
        phone: 'Моля въведете валиден телефонен номер',
        minLength: (min) => `Минимална дължина: ${min} символа`,
        maxLength: (max) => `Максимална дължина: ${max} символа`,
        pattern: 'Невалиден формат',
        date: 'Моля въведете валидна дата',
        time: 'Моля въведете валидно време',
        pastDate: 'Датата не може да бъде в миналото',
        futureDate: 'Датата трябва да бъде в бъдещето',
    },

    /**
     * Validate a single field
     * @param {HTMLElement} field - Input field to validate
     * @returns {Object} {valid: boolean, message: string}
     */
    validateField: function(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.hasAttribute('required');

        // Check required
        if (required && !value) {
            return { valid: false, message: this.messages.required };
        }

        // If empty and not required, it's valid
        if (!value) {
            return { valid: true, message: '' };
        }

        // Email validation
        if (type === 'email' || field.name.includes('email')) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                return { valid: false, message: this.messages.email };
            }
        }

        // Phone validation (Bulgarian format)
        if (type === 'tel' || field.name.includes('phone')) {
            const phoneRegex = /^(\+359|0)[0-9]{9}$/;
            if (!phoneRegex.test(value.replace(/[\s-]/g, ''))) {
                return { valid: false, message: this.messages.phone };
            }
        }

        // Date validation
        if (type === 'date') {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (field.hasAttribute('data-future-only') && selectedDate < today) {
                return { valid: false, message: this.messages.futureDate };
            }

            if (field.hasAttribute('data-past-only') && selectedDate > today) {
                return { valid: false, message: this.messages.pastDate };
            }
        }

        // Min length
        const minLength = field.getAttribute('minlength');
        if (minLength && value.length < parseInt(minLength)) {
            return { valid: false, message: this.messages.minLength(minLength) };
        }

        // Max length
        const maxLength = field.getAttribute('maxlength');
        if (maxLength && value.length > parseInt(maxLength)) {
            return { valid: false, message: this.messages.maxLength(maxLength) };
        }

        // Pattern validation
        const pattern = field.getAttribute('pattern');
        if (pattern) {
            const regex = new RegExp(pattern);
            if (!regex.test(value)) {
                return { valid: false, message: field.getAttribute('data-pattern-message') || this.messages.pattern };
            }
        }

        return { valid: true, message: '' };
    },

    /**
     * Show validation error for a field
     * @param {HTMLElement} field - Input field
     * @param {string} message - Error message
     */
    showError: function(field, message) {
        // Remove existing error
        this.clearError(field);

        // Add error class to field
        field.classList.add('is-invalid');

        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        errorDiv.setAttribute('data-validation-error', 'true');

        // Insert after field
        field.parentNode.appendChild(errorDiv);
    },

    /**
     * Clear validation error for a field
     * @param {HTMLElement} field - Input field
     */
    clearError: function(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');

        // Remove error message
        const errorDiv = field.parentNode.querySelector('[data-validation-error]');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    /**
     * Validate entire form
     * @param {HTMLFormElement} form - Form to validate
     * @returns {boolean} True if valid
     */
    validateForm: function(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            // Skip hidden and disabled fields
            if (field.type === 'hidden' || field.disabled) {
                return;
            }

            const result = this.validateField(field);

            if (!result.valid) {
                this.showError(field, result.message);
                isValid = false;

                // Focus first invalid field
                if (isValid === false && field === fields[0]) {
                    field.focus();
                }
            } else if (field.value.trim()) {
                this.clearError(field);
            }
        });

        return isValid;
    },

    /**
     * Setup real-time validation for a form
     * @param {HTMLFormElement|string} form - Form element or selector
     */
    setupRealtimeValidation: function(form) {
        const formElement = typeof form === 'string' ? document.querySelector(form) : form;

        if (!formElement) return;

        const fields = formElement.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            // Validate on blur
            field.addEventListener('blur', () => {
                if (field.value.trim()) {
                    const result = this.validateField(field);
                    if (!result.valid) {
                        this.showError(field, result.message);
                    } else {
                        this.clearError(field);
                    }
                }
            });

            // Clear error on input
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    const result = this.validateField(field);
                    if (result.valid) {
                        this.clearError(field);
                    }
                }
            });
        });

        // Validate on submit
        formElement.addEventListener('submit', (e) => {
            if (!this.validateForm(formElement)) {
                e.preventDefault();
                Toast.warning('Моля попълнете всички задължителни полета правилно');
            }
        });
    }
};

// Make FormValidator available globally
window.Validator = FormValidator;

// Auto-setup validation for forms with data-validate attribute
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        FormValidator.setupRealtimeValidation(form);
    });
});
