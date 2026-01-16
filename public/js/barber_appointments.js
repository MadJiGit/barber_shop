/**
 * Barber Appointments Management
 * Handles completing and cancelling appointments from barber's view
 */

// Get current locale
const currentLocale = document.documentElement.lang || 'bg';

// Localized strings
const barberAppointmentStrings = {
    'bg': {
        completeTitle: 'Завършване на час',
        completeText: 'Сигурни ли сте, че искате да отбележите този час като завършен?',
        completeConfirm: 'Да, завърши',
        cancelTitle: 'Отмяна на час',
        cancelText: 'Сигурни ли сте, че искате да отмените този час? Клиентът ще бъде уведомен.',
        cancelConfirm: 'Да, отмени',
        cancelButton: 'Отказ',
        successComplete: 'Часът е отбележен като завършен!',
        successCancel: 'Часът е отменен успешно!',
        errorComplete: 'Грешка при завършване на час',
        errorCancel: 'Грешка при отменяне на час',
        unknownError: 'Неизвестна грешка'
    },
    'en': {
        completeTitle: 'Complete Appointment',
        completeText: 'Are you sure you want to mark this appointment as completed?',
        completeConfirm: 'Yes, complete',
        cancelTitle: 'Cancel Appointment',
        cancelText: 'Are you sure you want to cancel this appointment? The client will be notified.',
        cancelConfirm: 'Yes, cancel',
        cancelButton: 'Cancel',
        successComplete: 'Appointment marked as completed!',
        successCancel: 'Appointment cancelled successfully!',
        errorComplete: 'Error completing appointment',
        errorCancel: 'Error cancelling appointment',
        unknownError: 'Unknown error'
    }
};

const strings = barberAppointmentStrings[currentLocale] || barberAppointmentStrings['bg'];

/**
 * Mark appointment as completed
 */
function completeAppointment(appointmentId, csrfToken) {
    Swal.fire({
        title: strings.completeTitle,
        text: strings.completeText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: strings.completeConfirm,
        cancelButtonText: strings.cancelButton,
        background: '#1a1a1a',
        color: '#ffffff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('_token', csrfToken);

            fetch(`/appointment/${appointmentId}/complete`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toast.success(strings.successComplete);
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentTab = urlParams.get('tab') || 'appointments';
                        window.location.href = window.location.pathname + '?tab=' + currentTab;
                    }, 1000);
                } else {
                    Toast.error(data.error || strings.unknownError);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.error(strings.errorComplete);
            });
        }
    });
}

/**
 * Cancel appointment from barber's side
 */
function cancelBarberAppointment(appointmentId, csrfToken) {
    Swal.fire({
        title: strings.cancelTitle,
        text: strings.cancelText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: strings.cancelConfirm,
        cancelButtonText: strings.cancelButton,
        background: '#1a1a1a',
        color: '#ffffff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('_token', csrfToken);

            fetch(`/appointment/${appointmentId}/barber-cancel`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toast.success(strings.successCancel);
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentTab = urlParams.get('tab') || 'appointments';
                        window.location.href = window.location.pathname + '?tab=' + currentTab;
                    }, 1000);
                } else {
                    Toast.error(data.error || strings.unknownError);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.error(strings.errorCancel);
            });
        }
    });
}
