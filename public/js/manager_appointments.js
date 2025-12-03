/**
 * Manager Appointments Management
 * Handles appointment editing and cancellation from manager's view
 */

// Load all procedures on page load
let allProcedures = [];

document.addEventListener('DOMContentLoaded', function() {
    // Load procedures
    loadProcedures();

    // Remove nice-select wrappers if they exist (they interfere with dynamic updates)
    removeNiceSelectWrappers();

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
 * Remove nice-select wrappers from modal selects
 */
function removeNiceSelectWrappers() {
    const selects = ['edit_barber', 'edit_procedure', 'edit_status'];

    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (!select) return;

        const niceSelectWrapper = select.nextElementSibling;
        if (niceSelectWrapper && niceSelectWrapper.classList.contains('nice-select')) {
            niceSelectWrapper.remove();
            select.style.display = 'block';
        }
    });
}

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
    Loading.show('Зареждане на данни...');

    fetch(`/manager/appointment/${appointmentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Toast.error(data.error);
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

            // Set min datetime to prevent selecting past times
            setMinDateTime();

            // Load procedures for selected barber
            loadProceduresForBarber(data.barber.id, data.procedure.id);

            // Show modal
            $('#editAppointmentModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.error('Грешка при зареждане на данните');
        })
        .finally(() => Loading.hide());
}

/**
 * Set minimum date/time to prevent selecting past appointments
 */
function setMinDateTime() {
    const dateInput = document.getElementById('edit_date');
    const timeInput = document.getElementById('edit_time');

    const now = new Date();
    const today = now.toISOString().split('T')[0];
    const currentTime = now.toTimeString().slice(0, 5);

    // Set min date to today
    dateInput.setAttribute('min', today);

    // When date changes, validate time
    dateInput.addEventListener('change', function() {
        validateDateTime();
    });

    timeInput.addEventListener('change', function() {
        validateDateTime();
    });

    function validateDateTime() {
        const selectedDate = dateInput.value;
        const selectedTime = timeInput.value;

        if (selectedDate === today) {
            // If today is selected, set min time to current time
            timeInput.setAttribute('min', currentTime);

            // Check if selected time is in the past
            if (selectedTime && selectedTime < currentTime) {
                Toast.warning('Не можете да изберете минал час за днес!');
                timeInput.value = '';
            }
        } else {
            // If future date, no time restriction
            timeInput.removeAttribute('min');
        }
    }

    // Initial validation
    validateDateTime();
}

/**
 * Load procedures that barber can perform
 */
function loadProceduresForBarber(barberId, selectedProcedureId = null) {
    // Load all available procedures
    fetch('/manager/api/procedures')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load procedures');
            }
            return response.json();
        })
        .then(procedures => {
            const select = document.getElementById('edit_procedure');
            select.innerHTML = '<option value="">Избери процедура</option>';

            procedures.forEach(proc => {
                const option = document.createElement('option');
                option.value = proc.id;
                // Show both master and junior prices/duration
                option.textContent = `${proc.type} (Master: ${proc.duration_master}мин/${proc.price_master}лв, Junior: ${proc.duration_junior}мин/${proc.price_junior}лв)`;
                if (proc.id === selectedProcedureId) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading procedures:', error);
            Toast.error('Грешка при зареждане на процедурите. Моля опитайте отново.');
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
        Toast.warning('Моля попълнете всички задължителни полета!');
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
            Toast.success('Часът е обновен успешно!');
            $('#editAppointmentModal').modal('hide');
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.error(data.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('Грешка при запазване на промените');
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
            Toast.success('Часът е отменен успешно!');
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.error(data.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('Грешка при отменяне на час');
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

/**
 * Column Visibility Management
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load column visibility from localStorage
    loadColumnVisibility();

    // Attach toggle event listeners
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const column = this.dataset.column;
            const isVisible = this.checked;
            toggleColumn(column, isVisible);
            saveColumnVisibility();
        });
    });
});

function toggleColumn(columnName, isVisible) {
    const headerCells = document.querySelectorAll(`th.col-${columnName}`);
    const bodyCells = document.querySelectorAll(`td.col-${columnName}`);

    const displayValue = isVisible ? '' : 'none';

    headerCells.forEach(cell => cell.style.display = displayValue);
    bodyCells.forEach(cell => cell.style.display = displayValue);
}

function saveColumnVisibility() {
    const visibility = {};
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        visibility[checkbox.dataset.column] = checkbox.checked;
    });
    localStorage.setItem('managerAppointmentsColumns', JSON.stringify(visibility));
}

function loadColumnVisibility() {
    const saved = localStorage.getItem('managerAppointmentsColumns');
    if (!saved) return;

    try {
        const visibility = JSON.parse(saved);
        Object.entries(visibility).forEach(([column, isVisible]) => {
            const checkbox = document.querySelector(`.column-toggle[data-column="${column}"]`);
            if (checkbox) {
                checkbox.checked = isVisible;
                toggleColumn(column, isVisible);
            }
        });
    } catch (e) {
        console.error('Error loading column visibility:', e);
    }
}

/**
 * Change per-page value and reload
 */
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('perPage', value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
