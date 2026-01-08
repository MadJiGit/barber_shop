/**
 * Barber Calendar - JavaScript
 */

// Global variables
let currentDate = null;
let currentSlots = [];

// Note: Tab activation is handled by barber_profile.js based on URL parameters

/**
 * Open day modal to edit schedule
 */
function openDayModal(date) {
    currentDate = date;
    const modal = $('#dayScheduleModal');

    // Check if date is in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(date);
    selectedDate.setHours(0, 0, 0, 0);
    const isPast = selectedDate < today;

    // Reset modal
    $('#modalLoading').show();
    $('#modalContent').hide();

    // Format date for display
    const dateObj = new Date(date);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    $('#modalDate').text(dateObj.toLocaleDateString('bg-BG', options));

    modal.modal('show');

    // Load day schedule via AJAX
    fetch(`/barber/schedule/day/${date}`)
        .then(response => response.json())
        .then(data => {
            $('#modalDayName').text(data.dayOfWeek);
            currentSlots = data.slots;

            renderTimeSlots(data.slots);

            // Make modal read-only for past dates
            if (isPast) {
                makeModalReadOnly();
            } else {
                makeModalEditable();
            }

            $('#modalLoading').hide();
            $('#modalContent').show();
        })
        .catch(error => {
            console.error('Error loading schedule:', error);
            Toast.error('Грешка при зареждане на графика');
            modal.modal('hide');
        });
}

/**
 * Make modal read-only (for past dates)
 */
function makeModalReadOnly() {
    // Disable all inputs
    $('#workingCheckbox').prop('disabled', true);
    $('#startTime').prop('disabled', true);
    $('#endTime').prop('disabled', true);
    $('#scheduleReason').prop('disabled', true);
    $('#timeSlotsContainer input[type="checkbox"]').prop('disabled', true);

    // Hide save button, show read-only message
    $('.modal-footer button.btn-primary').hide();

    // Add read-only indicator if not exists
    if (!$('#readOnlyIndicator').length) {
        $('.modal-header .modal-title').append(' <span id="readOnlyIndicator" class="badge badge-secondary ml-2">🔒 Само за преглед</span>');
    }
}

/**
 * Make modal editable (for future dates)
 */
function makeModalEditable() {
    // Enable all inputs
    $('#workingCheckbox').prop('disabled', false);
    $('#startTime').prop('disabled', false);
    $('#endTime').prop('disabled', false);
    $('#scheduleReason').prop('disabled', false);

    // Show save button
    $('.modal-footer button.btn-primary').show();

    // Remove read-only indicator
    $('#readOnlyIndicator').remove();
}

/**
 * Render time slots in modal
 */
function renderTimeSlots(slots) {
    const container = $('#timeSlotsContainer');
    container.empty();

    if (slots.length === 0) {
        container.html('<p class="text-muted">Този ден не е работен по подразбиране.</p>');
        $('#workingCheckbox').prop('checked', false);
        $('#workingHoursSection').hide();
        return;
    }

    $('#workingCheckbox').prop('checked', true);
    $('#workingHoursSection').show();

    slots.forEach(slot => {
        const div = $('<div>').addClass('time-slot-checkbox');

        if (slot.locked) {
            div.addClass('locked');
            div.html(`
                <input type="checkbox" disabled checked>
                <label>
                    ${slot.time}
                    <span class="client-name">🔒 ${slot.client || 'Зает'}</span>
                </label>
            `);
        } else {
            const checked = slot.available ? 'checked' : '';
            div.html(`
                <input type="checkbox" ${checked} data-time="${slot.time}">
                <label>${slot.time}</label>
            `);
        }

        container.append(div);
    });
}

/**
 * Working checkbox toggle
 */
$(document).on('change', '#workingCheckbox', function() {
    if ($(this).is(':checked')) {
        $('#workingHoursSection').show();
    } else {
        $('#workingHoursSection').hide();
    }
});

/**
 * Save schedule via AJAX
 */
function saveSchedule() {
    const isWorking = $('#workingCheckbox').is(':checked');
    const startTime = $('#startTime').val();
    const endTime = $('#endTime').val();
    const reason = $('#scheduleReason').val();

    // Get excluded slots (unchecked available slots)
    const excludedSlots = [];
    $('#timeSlotsContainer input[type="checkbox"]:not(:disabled)').each(function() {
        if (!$(this).is(':checked')) {
            excludedSlots.push($(this).data('time'));
        }
    });

    const data = {
        date: currentDate,
        is_available: isWorking,
        start_time: isWorking ? startTime : null,
        end_time: isWorking ? endTime : null,
        excluded_slots: excludedSlots.length > 0 ? excludedSlots : null,
        reason: reason || null
    };

    fetch('/barber/schedule/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Toast.success('Графикът е запазен успешно!');

            // Build redirect URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentTab = urlParams.get('tab') || 'calendar';
            const year = urlParams.get('year') || '';
            const month = urlParams.get('month') || '';
            let url = window.location.pathname + '?tab=' + currentTab;
            if (year) url += '&year=' + year;
            if (month) url += '&month=' + month;

            // Wait for modal to fully close before redirect
            $('#dayScheduleModal').one('hidden.bs.modal', function () {
                window.location.href = url;
            });
            $('#dayScheduleModal').modal('hide');
        } else {
            Toast.error(result.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error saving schedule:', error);
        Toast.error('Грешка при запазване на графика');
    });
}
