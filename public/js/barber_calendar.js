/**
 * Barber Calendar - JavaScript
 */

// Global variables
let currentDate = null;
let currentSlots = [];

/**
 * Activate first tab on load for barbers
 */
$(document).ready(function() {
    if ($('#calendar-tab').length) {
        $('#calendar-tab').addClass('active');
        $('#calendar').addClass('show active');
    }
});

/**
 * Open day modal to edit schedule
 */
function openDayModal(date) {
    currentDate = date;
    const modal = $('#dayScheduleModal');

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
            $('#dayScheduleModal').modal('hide');
            // Preserve active tab on reload
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const currentTab = urlParams.get('tab') || 'calendar';
                const year = urlParams.get('year') || '';
                const month = urlParams.get('month') || '';
                let url = window.location.pathname + '?tab=' + currentTab;
                if (year) url += '&year=' + year;
                if (month) url += '&month=' + month;
                window.location.href = url;
            }, 1000);
        } else {
            Toast.error(result.error || 'Неизвестна грешка');
        }
    })
    .catch(error => {
        console.error('Error saving schedule:', error);
        Toast.error('Грешка при запазване на графика');
    });
}
