/**
 * Toast Notification System using SweetAlert2
 * Wrapper for consistent API across the application
 */

/**
 * Show toast notification using SweetAlert2
 * @param {string} message - The message to display
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duration in ms (default: 4000)
 */
function showToast(message, type = 'info', duration = 4000) {
    // Map type to SweetAlert2 icons
    const iconMap = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    // Map type to translated titles (from window.toastTranslations)
    const titleMap = window.toastTranslations || {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Information'
    };

    const icon = iconMap[type] || 'info';
    const title = titleMap[type] || titleMap.info;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        text: message,
        showConfirmButton: false,
        timer: duration,
        timerProgressBar: true,

        // Custom styling for barber shop theme
        background: '#1a1a1a',
        color: '#ffffff',
        iconColor: '#d4a373',

        customClass: {
            popup: 'barber-toast',
            title: 'barber-toast-title',
            htmlContainer: 'barber-toast-text',
            timerProgressBar: 'barber-toast-progress'
        },

        // Animation
        showClass: {
            popup: 'swal2-show',
            backdrop: 'swal2-backdrop-show',
            icon: 'swal2-icon-show'
        },
        hideClass: {
            popup: 'swal2-hide'
        }
    });
}

/**
 * Convenience methods for different toast types
 */
let Toast = {
    success: (message, duration) => showToast(message, 'success', duration),
    error: (message, duration) => showToast(message, 'error', duration),
    warning: (message, duration) => showToast(message, 'warning', duration),
    info: (message, duration) => showToast(message, 'info', duration)
};

// Make Toast available globally
window.Toast = Toast;
window.showToast = showToast;
