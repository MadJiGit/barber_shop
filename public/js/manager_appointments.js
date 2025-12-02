/**
 * Manager Appointments Management
 * Handles appointment editing and cancellation from manager's view
 */

// Load all procedures on page load
let allProcedures = [];

document.addEventListener('DOMContentLoaded', function() {
    // Load procedures
    loadProcedures();

    // Attach event listeners to edit buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const appointmentId = this.dataset.appointmentId;
            openEditModal(appointmentId);
        });
    });

    // Attach event listeners to cancel buttons
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const appointmentId = this.dataset.appointmentId;
            cancelAppointment(appointmentId);
        });
    });

    // Save button handler
    document.getElementById('saveAppointmentBtn').addEventListener('click', saveAppointment);
});

/**
 * Load all procedures from database
 */
function loadProcedures() {
    // For now, we'll load procedures when modal opens
    // In production, you might want to pre-load this or use API endpoint
}

/**
 * Open edit modal and load appointment details
 */
function openEditModal(appointmentId) {
    fetch(`/manager/appointment/${appointmentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Грешка: ' + data.error);
                return;
            }

            // Populate form fields
            document.getElementById('edit_appointment_id').value = data.id;
            document.getElementById('edit_client').value = `${data.client.name} (${data.client.email})`;
            document.getElementById('edit_barber').value = data.barber.id;
            document.getElementById('edit_date').value = data.date;
            document.getElementById('edit_time').value = data.time;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_notes').value = data.notes || '';

            // Load procedures for selected barber
            loadProceduresForBarber(data.barber.id, data.procedure.id);

            // Show modal
            $('#editAppointmentModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Грешка при зареждане на данните');
        });
}

/**
 * Load procedures that barber can perform
 */
function loadProceduresForBarber(barberId, selectedProcedureId = null) {
    // For now, load all procedures
    // TODO: Filter by barber capability via API endpoint
    fetch('/api/procedures') // You'll need to create this endpoint
        .then(response => response.json())
        .then(procedures => {
            const select = document.getElementById('edit_procedure');
            select.innerHTML = '<option value="">Избери процедура</option>';

            procedures.forEach(proc => {
                const option = document.createElement('option');
                option.value = proc.id;
                option.textContent = `${proc.type} (${proc.duration} мин)`;
                if (proc.id == selectedProcedureId) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading procedures:', error);
            // Fallback: set selected procedure manually
            const select = document.getElementById('edit_procedure');
            if (selectedProcedureId) {
                const option = document.createElement('option');
                option.value = selectedProcedureId;
                option.selected = true;
                select.appendChild(option);
            }
        });
}

/**
 * Save appointment changes
 */
function saveAppointment() {
    const appointmentId = document.getElementById('edit_appointment_id').value;
    const data = {
        barber_id: parseInt(document.getElementById('edit_barber').value),
        date: document.getElementById('edit_date').value,
        time: document.getElementById('edit_time').value,
        procedure_id: parseInt(document.getElementById('edit_procedure').value),
        status: document.getElementById('edit_status').value,
        notes: document.getElementById('edit_notes').value,
    };

    // Validate
    if (!data.barber_id || !data.date || !data.time || !data.procedure_id) {
        alert('Моля попълнете всички задължителни полета!');
        return;
    }

    // Show loading state
    const saveBtn = document.getElementById('saveAppointmentBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Запазване...';
    saveBtn.disabled = true;

    fetch(`/manager/appointment/${appointmentId}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Часът е обновен успешно!');
            $('#editAppointmentModal').modal('hide');
            location.reload();
        } else {
            alert('Грешка: ' + (data.error || 'Неизвестна грешка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Грешка при запазване на промените');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

/**
 * Cancel appointment
 */
function cancelAppointment(appointmentId) {
    const reason = prompt('Причина за отмяна (незадължително):');
    if (reason === null) {
        return; // User cancelled
    }

    if (!confirm('Сигурни ли сте, че искате да отмените този час?')) {
        return;
    }

    fetch(`/manager/appointment/${appointmentId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reason: reason || 'Отменен от мениджър'
        })
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

// Listen for barber change to reload procedures
document.addEventListener('DOMContentLoaded', function() {
    const barberSelect = document.getElementById('edit_barber');
    if (barberSelect) {
        barberSelect.addEventListener('change', function() {
            loadProceduresForBarber(this.value);
        });
    }
});
