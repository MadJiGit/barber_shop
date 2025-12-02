/**
 * Barber Appointments Management
 * Handles completing and cancelling appointments from barber's view
 */

/**
 * Mark appointment as completed
 */
function completeAppointment(appointmentId) {
    if (!confirm('Сигурни ли сте, че искате да отбележите този час като завършен?')) {
        return;
    }

    fetch(`/barber/appointment/${appointmentId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Toast.success('Часът е отбележен като завършен!');
            // Preserve active tab on reload
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const currentTab = urlParams.get('tab') || 'appointments';
                window.location.href = window.location.pathname + '?tab=' + currentTab;
            }, 1000);
        } else {
            Toast.error(data.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('Грешка при завършване на час');
    });
}

/**
 * Cancel appointment from barber's side
 */
function cancelBarberAppointment(appointmentId) {
    if (!confirm('Сигурни ли сте, че искате да отмените този час? Клиентът ще бъде уведомен.')) {
        return;
    }

    fetch(`/barber/appointment/${appointmentId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Toast.success('Часът е отменен успешно!');
            // Preserve active tab on reload
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const currentTab = urlParams.get('tab') || 'appointments';
                window.location.href = window.location.pathname + '?tab=' + currentTab;
            }, 1000);
        } else {
            Toast.error(data.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('Грешка при отменяне на час');
    });
}
