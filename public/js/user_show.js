/**
 * User Profile Show - JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const backButton = document.getElementById('backButton');
    const form = document.getElementById('profileForm');

    if (backButton && form) {
        let formChanged = false;

        // Track form changes
        form.addEventListener('input', function() {
            formChanged = true;
        });

        // Confirm before leaving if form has changes
        backButton.addEventListener('click', function(e) {
            if (formChanged) {
                if (!confirm('Имате незапазени промени. Сигурни ли сте, че искате да напуснете без да запазите?')) {
                    e.preventDefault();
                }
            }
        });

        // Reset formChanged on successful submit
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    }
});

/**
 * Cancel appointment function
 */
function cancelAppointment(appointmentId, csrfToken) {
    if (confirm('Сигурни ли сте, че искате да отмените това посещение?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/appointment/' + appointmentId + '/cancel';

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}
