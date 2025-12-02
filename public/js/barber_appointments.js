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

    // TODO: Implement AJAX call to complete appointment
    fetch(`/barber/appointment/${appointmentId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Часът е отбележен като завършен!');
            location.reload();
        } else {
            alert('Грешка: ' + (data.error || 'Неизвестна грешка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Грешка при завършване на час');
    });
}

/**
 * Cancel appointment from barber's side
 */
function cancelBarberAppointment(appointmentId) {
    if (!confirm('Сигурни ли сте, че искате да отмените този час? Клиентът ще бъде уведомен.')) {
        return;
    }

    // TODO: Implement AJAX call to cancel appointment
    fetch(`/barber/appointment/${appointmentId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Часът е отменен успешно!');
            location.reload();
        } else {
            alert('Грешка: ' + (data.error || 'Неизвестна грешка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Грешка при отменяне на час');
    });
}
