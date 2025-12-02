/**
 * Appointment Form - Client-side logic
 * Handles procedure selection, barber filtering, time slot management, and date navigation
 */

// Global state
let currentDate;
let selectedProcedure = null;
let selectedBarber = null;
let selectedTime = null;
let occupiedSlots = {};
let barberProcedureMap = {};
let appointmentsData = [];
let today = '';

// Bulgarian day names
const dayNamesBg = ['Неделя', 'Понеделник', 'Вторник', 'Сряда', 'Четвъртък', 'Петък', 'Събота'];

/**
 * Initialize the appointment form with data from server
 */
function initializeAppointmentForm(data) {
    appointmentsData = data.appointments || [];
    today = data.today;
    currentDate = new Date(today);
    occupiedSlots = data.occupiedSlots || {};
    barberProcedureMap = data.barberProcedureMap || {};
}

/**
 * Handle procedure selection change
 */
function handleProcedureChange() {
    const select = document.getElementById('procedure_select');
    const procedureId = select.value;

    if (procedureId) {
        selectedProcedure = {
            id: procedureId,
            name: select.options[select.selectedIndex].text,
            durationMaster: parseInt(select.options[select.selectedIndex].dataset.durationMaster),
            durationJunior: parseInt(select.options[select.selectedIndex].dataset.durationJunior)
        };
        document.getElementById('selected_procedure_id').value = procedureId;

        // Filter barbers by selected procedure
        filterBarbersByProcedure(parseInt(procedureId));

        updateSelectionSummary();
    } else {
        selectedProcedure = null;
        document.getElementById('selected_procedure_id').value = '';

        // Show all barbers if no procedure selected
        showAllBarbers();
    }

    // Clear barber selection when procedure changes
    selectedBarber = null;
    selectedTime = null;
    document.getElementById('selected_barber_id').value = '';
    document.getElementById('pickedHours').value = '';

    // Clear all time slot selections
    document.querySelectorAll('.time-slot-box.selected').forEach(btn => {
        btn.classList.remove('selected');
    });

    checkFormValidity();
}

/**
 * Filter barbers based on selected procedure
 */
function filterBarbersByProcedure(procedureId) {
    document.querySelectorAll('.barber-section').forEach(section => {
        const barberId = parseInt(section.dataset.barberId);
        const barberProcedures = barberProcedureMap[barberId] || [];

        if (barberProcedures.includes(procedureId)) {
            section.style.display = 'block';
            // Filter time slots for this barber based on procedure duration
            filterTimeSlotsForBarber(section, barberId);
        } else {
            section.style.display = 'none';
        }
    });
}

/**
 * Filter and manage time slots based on procedure duration
 * Simple logic: Show all 30-min slots, disable those without enough consecutive free time
 */
function filterTimeSlotsForBarber(barberSection, barberId) {
    if (!selectedProcedure) {
        // No procedure selected - show all slots as available
        barberSection.querySelectorAll('.time-slot-box').forEach(slot => {
            slot.style.display = '';
            slot.disabled = false;
            slot.classList.remove('insufficient-time');
            if (slot.dataset.occupied !== 'true') {
                slot.classList.add('available');
            }
        });
        return;
    }

    // Get duration based on barber level
    const isBarberJunior = barberSection.dataset.barberJunior === 'true';
    const procedureDuration = isBarberJunior
        ? selectedProcedure.durationJunior
        : selectedProcedure.durationMaster;
    const slotsNeeded = Math.ceil(procedureDuration / 30);

    const allSlots = Array.from(barberSection.querySelectorAll('.time-slot-box'));

    allSlots.forEach((slot, index) => {
        const isOccupied = slot.dataset.occupied === 'true';

        // Reset slot classes
        slot.style.display = '';
        slot.classList.remove('insufficient-time');

        // Skip occupied slots - they're already marked
        if (isOccupied) {
            return;
        }

        // Check if there's enough consecutive free time for this procedure
        let hasEnoughSpace = true;

        // Check next N slots (including current slot)
        for (let i = 0; i < slotsNeeded; i++) {
            const nextSlot = allSlots[index + i];
            if (!nextSlot || nextSlot.dataset.occupied === 'true') {
                hasEnoughSpace = false;
                break;
            }
        }

        // If not enough space, disable and mark this slot
        if (!hasEnoughSpace) {
            slot.disabled = true;
            slot.classList.add('insufficient-time');
            slot.classList.remove('available');
        } else {
            // Enable slot - there's enough free time
            slot.disabled = false;
            slot.classList.add('available');
            slot.classList.remove('insufficient-time');
        }
    });
}

/**
 * Show all barbers (when no procedure selected)
 */
function showAllBarbers() {
    document.querySelectorAll('.barber-section').forEach(section => {
        section.style.display = 'block';
    });
}

/**
 * Change date (increment/decrement days)
 * IMPORTANT: Reloads page with new date parameter
 */
function changeDate(days) {
    const newDate = new Date(currentDate);
    newDate.setDate(newDate.getDate() + days);

    // Don't allow selecting dates in the past
    const todayDate = new Date(new Date().toDateString()); // Normalize to midnight
    if (newDate < todayDate) {
        return; // Block navigation to past dates
    }

    const dateStr = newDate.toISOString().split('T')[0];

    // Reload page with new date parameter and preserve selected procedure
    const url = new URL(window.location);
    url.searchParams.set('date', dateStr);

    // Preserve selected procedure in URL
    if (selectedProcedure && selectedProcedure.id) {
        url.searchParams.set('procedure', selectedProcedure.id);
    }

    window.location.href = url.toString();
}

/**
 * Load occupied slots via AJAX
 */
function loadOccupiedSlots(date) {
    fetch('/api/occupied-slots/' + date)
        .then(response => response.json())
        .then(data => {
            occupiedSlots = data;
            updateTimeSlots();
        })
        .catch(error => {
            console.error('Error loading occupied slots:', error);
        });
}

/**
 * Update time slots based on occupied slots
 */
function updateTimeSlots() {
    // First, check which barbers are completely unavailable
    const unavailableBarbers = [];
    for (const barberId in occupiedSlots) {
        if (occupiedSlots[barberId].includes('__FULL_DAY_OFF__')) {
            unavailableBarbers.push(barberId);
        }
    }

    // Hide barber sections that are completely unavailable OR don't perform selected procedure
    document.querySelectorAll('.barber-section').forEach(section => {
        const barberId = parseInt(section.dataset.barberId);

        // Hide if full day off
        if (unavailableBarbers.includes(barberId.toString())) {
            section.style.display = 'none';
            return;
        }

        // If procedure is selected, check if barber can perform it
        if (selectedProcedure && selectedProcedure.id) {
            const barberProcedures = barberProcedureMap[barberId] || [];
            const procedureId = parseInt(selectedProcedure.id);

            if (barberProcedures.includes(procedureId)) {
                section.style.display = 'block';
                // Re-filter time slots for this barber
                filterTimeSlotsForBarber(section, barberId);
            } else {
                section.style.display = 'none';
            }
        } else {
            // No procedure selected - show all available barbers
            section.style.display = 'block';
        }
    });

    // Update individual time slots for available barbers
    document.querySelectorAll('.time-slot-box').forEach(button => {
        const section = button.closest('.barber-section');
        if (!section || section.style.display === 'none') {
            return; // Skip hidden barbers
        }

        const barberId = section.dataset.barberId;
        const timeValue = button.querySelector('.time-label').textContent;
        const isOccupied = occupiedSlots[barberId] && occupiedSlots[barberId].includes(timeValue);

        // Update button state
        if (isOccupied) {
            button.classList.remove('available', 'selected');
            button.classList.add('booked');
            button.disabled = true;
            button.dataset.occupied = 'true';
        } else {
            button.classList.remove('booked', 'selected');
            button.classList.add('available');
            button.disabled = false;
            button.dataset.occupied = 'false';
        }
    });

    // Clear selection when slots update
    selectedTime = null;
    selectedBarber = null;
    document.getElementById('selected_barber_id').value = '';
    document.getElementById('pickedHours').value = '';
    updateSelectionSummary();
    checkFormValidity();
}

/**
 * Update date navigation buttons (disable left arrow if on today)
 */
function updateDateNavigationButtons() {
    const todayDate = new Date(new Date().toDateString());
    const leftArrow = document.querySelector('.date-arrow-btn-new:first-child');

    if (currentDate <= todayDate) {
        leftArrow.disabled = true;
        leftArrow.style.opacity = '0.3';
        leftArrow.style.cursor = 'not-allowed';
    } else {
        leftArrow.disabled = false;
        leftArrow.style.opacity = '1';
        leftArrow.style.cursor = 'pointer';
    }
}

/**
 * Select time slot
 */
function selectTimeSlot(button, barberId, time) {
    // Remove selection from all slots
    document.querySelectorAll('.time-slot-box').forEach(btn => {
        btn.classList.remove('selected');
    });

    // Add selection to clicked slot
    button.classList.add('selected');

    selectedBarber = barberId;
    selectedTime = time;

    document.getElementById('selected_barber_id').value = barberId;
    document.getElementById('pickedHours').value = time;

    updateSelectionSummary();
    checkFormValidity();
}

/**
 * Update selection summary
 */
function updateSelectionSummary() {
    const summary = document.getElementById('selection_summary');
    let text = '';

    if (selectedProcedure && selectedBarber && selectedTime) {
        const barberElement = document.querySelector(`[data-barber-id="${selectedBarber}"] .barber-name`);
        const barberName = barberElement ? barberElement.textContent.trim() : 'Избран бръснар';
        text = `Избрано: ${selectedProcedure.name} с ${barberName} в ${selectedTime}`;
        summary.innerHTML = `<strong>${text}</strong>`;
    } else {
        const missing = [];
        if (!selectedProcedure) missing.push('услуга');
        if (!selectedTime) missing.push('час');
        text = `Изберете ${missing.join(' и ')} за да продължите`;
        summary.innerHTML = `<small class="text-muted">${text}</small>`;
    }
}

/**
 * Check if form is valid and enable/disable submit button
 */
function checkFormValidity() {
    const submitBtn = document.getElementById('submit_btn');
    if (selectedProcedure && selectedBarber && selectedTime) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
    }
}

/**
 * Initialize on DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    const dayOfWeekElement = document.getElementById('day_of_week');
    if (dayOfWeekElement) {
        const dayOfWeek = dayNamesBg[currentDate.getDay()];
        dayOfWeekElement.textContent = dayOfWeek;
    }

    // Restore selected procedure from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const procedureIdFromUrl = urlParams.get('procedure');
    if (procedureIdFromUrl) {
        const procedureSelect = document.getElementById('procedure_select');
        if (procedureSelect) {
            procedureSelect.value = procedureIdFromUrl;
            // Trigger the change event to update the UI
            handleProcedureChange();
        }
    }

    // Initialize date navigation buttons state
    updateDateNavigationButtons();

    // Calculate max date (1 year from today)
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 1);

    // Initialize Gijgo datepicker with slight delay to ensure Gijgo is loaded
    setTimeout(function() {
        const calendarInput = $('#calendar_display');
        const calendarInputElement = document.getElementById('calendar_display');

        console.log('Attempting to initialize datepicker...');
        console.log('jQuery element exists:', calendarInput.length > 0);
        console.log('Datepicker function available:', typeof calendarInput.datepicker);

        if (calendarInput.length && typeof calendarInput.datepicker === 'function') {
            try {
                // Initialize datepicker first
                calendarInput.datepicker({
                    format: 'yyyy-mm-dd',
                    minDate: today,
                    maxDate: maxDate,
                    uiLibrary: 'bootstrap4',
                    showRightIcon: false,
                    change: function (e) {
                        const selectedDate = e.target.value;

                        // Reload page with new date and preserve selected procedure
                        const url = new URL(window.location);
                        url.searchParams.set('date', selectedDate);

                        // Preserve selected procedure in URL
                        if (selectedProcedure && selectedProcedure.id) {
                            url.searchParams.set('procedure', selectedProcedure.id);
                        }

                        window.location.href = url.toString();
                    },
                    open: function(e) {
                        console.log('Datepicker opened!');
                    }
                });
                console.log('Datepicker initialized successfully!');

                // NOW add click handler after Gijgo is initialized
                if (calendarInputElement) {
                    // Prevent keyboard input
                    calendarInputElement.addEventListener('keydown', function(e) {
                        e.preventDefault();
                        return false;
                    });

                    // Manually trigger datepicker open on click (since readonly blocks normal behavior)
                    // Use capture phase to catch event BEFORE it bubbles
                    calendarInputElement.addEventListener('click', function(e) {
                        console.log('Click detected on calendar input!');
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();

                        // Programmatically open the Gijgo datepicker
                        console.log('Attempting to open datepicker...');
                        console.log('Gijgo data:', calendarInput.data('gijgo'));

                        // Use setTimeout to ensure we're outside the current event loop
                        setTimeout(() => {
                            calendarInput.datepicker('open');
                        }, 10);

                        return false;
                    }, true); // <-- IMPORTANT: capture phase

                    console.log('Click handler attached!');
                }
            } catch (error) {
                console.error('Error initializing datepicker:', error);
            }
        } else {
            console.error('Cannot initialize datepicker - jQuery element or datepicker function not available');
        }
    }, 100);


    // Remove nice-select wrapper from our custom select
    const procedureSelect = document.getElementById('procedure_select');
    const niceSelectWrapper = procedureSelect.nextElementSibling;
    if (niceSelectWrapper && niceSelectWrapper.classList.contains('nice-select')) {
        niceSelectWrapper.remove();
        procedureSelect.style.display = 'block';
    }
});
