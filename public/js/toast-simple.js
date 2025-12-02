/**
 * Simple Toast Notification System
 * Pure JavaScript implementation - no Bootstrap dependency
 */

// Toast container - will be added to body on first use
let toastContainer = null;

/**
 * Initialize toast container
 */
function initToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container-custom';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duration in ms (default: 4000)
 */
function showToast(message, type = 'info', duration = 4000) {
    const container = initToastContainer();

    // Map type to classes and icons
    const typeConfig = {
        success: {
            className: 'toast-success',
            icon: 'fa-check-circle',
            title: 'Успех'
        },
        error: {
            className: 'toast-error',
            icon: 'fa-exclamation-circle',
            title: 'Грешка'
        },
        warning: {
            className: 'toast-warning',
            icon: 'fa-exclamation-triangle',
            title: 'Предупреждение'
        },
        info: {
            className: 'toast-info',
            icon: 'fa-info-circle',
            title: 'Информация'
        }
    };

    const config = typeConfig[type] || typeConfig.info;
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

    // Create toast element
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast-item ${config.className}`;
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${config.icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${config.title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast('${toastId}')">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add to container
    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('toast-show');
    }, 10);

    // Auto-hide after duration
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
}

/**
 * Close a specific toast
 * @param {string} toastId - Toast element ID
 */
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.remove('toast-show');
        toast.classList.add('toast-hide');

        // Remove from DOM after animation
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

/**
 * Convenience methods for different toast types
 */
const Toast = {
    success: (message, duration) => showToast(message, 'success', duration),
    error: (message, duration) => showToast(message, 'error', duration),
    warning: (message, duration) => showToast(message, 'warning', duration),
    info: (message, duration) => showToast(message, 'info', duration)
};

// Make Toast available globally
window.Toast = Toast;
window.closeToast = closeToast;
