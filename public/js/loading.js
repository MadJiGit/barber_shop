/**
 * Loading States Manager
 * Provides consistent loading indicators across the application
 */

const LoadingManager = {
    overlay: null,

    /**
     * Show full-page loading overlay
     * @param {string} message - Optional loading message
     */
    show: function(message = 'Зареждане...') {
        // Remove existing overlay if any
        this.hide();

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay fade-in';
        overlay.innerHTML = `
            <div class="spinner-container">
                <div class="spinner"></div>
                <div class="loading-text">${message}</div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.overlay = overlay;

        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
    },

    /**
     * Hide full-page loading overlay
     */
    hide: function() {
        if (this.overlay) {
            this.overlay.classList.add('fade-out');
            setTimeout(() => {
                if (this.overlay && this.overlay.parentNode) {
                    this.overlay.parentNode.removeChild(this.overlay);
                }
                this.overlay = null;
                document.body.style.overflow = '';
            }, 300);
        }
    },

    /**
     * Show button loading state
     * @param {HTMLElement} button - Button element
     * @param {string} loadingText - Text to show while loading
     * @returns {Function} Cleanup function to restore button state
     */
    buttonLoading: function(button, loadingText = 'Обработка...') {
        const originalHTML = button.innerHTML;
        const originalDisabled = button.disabled;

        button.disabled = true;
        button.innerHTML = `<span class="btn-spinner"></span>${loadingText}`;

        // Return cleanup function
        return function() {
            button.innerHTML = originalHTML;
            button.disabled = originalDisabled;
        };
    },

    /**
     * Show loading state for a card/section
     * @param {string|HTMLElement} element - Element or selector
     */
    cardLoading: function(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (el) {
            el.classList.add('loading-card');
        }
    },

    /**
     * Remove loading state from a card/section
     * @param {string|HTMLElement} element - Element or selector
     */
    cardLoaded: function(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (el) {
            el.classList.remove('loading-card');
        }
    },

    /**
     * Create skeleton loading placeholders
     * @param {number} count - Number of skeleton lines
     * @returns {string} HTML string with skeleton elements
     */
    createSkeleton: function(count = 3) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += '<div class="skeleton skeleton-line"></div>';
        }
        return html;
    },

    /**
     * Wrap a Promise with loading indicator
     * @param {Promise} promise - Promise to wrap
     * @param {string} message - Loading message
     * @returns {Promise}
     */
    wrapPromise: function(promise, message = 'Зареждане...') {
        this.show(message);

        return promise
            .finally(() => {
                this.hide();
            });
    }
};

// Make LoadingManager available globally
window.Loading = LoadingManager;

// Helper function for fetch requests with loading
window.fetchWithLoading = function(url, options = {}, loadingMessage = 'Зареждане...') {
    Loading.show(loadingMessage);

    return fetch(url, options)
        .finally(() => {
            Loading.hide();
        });
};
