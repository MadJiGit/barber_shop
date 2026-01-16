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
let barberWorkingHours = {};
let appointmentsData = [];
let barbersData = [];
let today = '';

// Day names by locale
const dayNames = {
    'bg': ['Неделя', 'Понеделник', 'Вторник', 'Сряда', 'Четвъртък', 'Петък', 'Събота'],
    'en': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
};

// Role translations
const roleTranslations = {
    'bg': {
        'ROLE_BARBER_SENIOR': 'Старши Бръснар',
        'ROLE_BARBER': 'Бръснар',
        'ROLE_BARBER_JUNIOR': 'Младши Бръснар'
    },
    'en': {
        'ROLE_BARBER_SENIOR': 'Senior Barber',
        'ROLE_BARBER': 'Barber',
        'ROLE_BARBER_JUNIOR': 'Junior Barber'
    }
};

// Get current locale (from document or default to 'bg')
const currentLocale = document.documentElement.lang || 'bg';

/**
 * Translate role key to localized string
 */
function translateRole(roleKey) {
    return roleTranslations[currentLocale]?.[roleKey] || roleKey;
}

/**
 * Convert date from yyyy-mm-dd to dd-MM-yyyy for display
 */
function formatDateForDisplay(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}-${month}-${year}`;
}

/**
 * Convert date from dd-MM-yyyy to yyyy-mm-dd for API/backend
 */
function formatDateForAPI(dateStr) {
    const parts = dateStr.split('-');
    const day = parts[0].padStart(2, '0');
    const month = parts[1].padStart(2, '0');
    const year = parts[2];
    return `${year}-${month}-${day}`;
}

/**
 * Update day of week display
 */
function updateDayOfWeek() {
    const dayOfWeekElement = document.getElementById('day_of_week');
    if (dayOfWeekElement) {
        const localizedDays = dayNames[currentLocale] || dayNames['bg'];
        dayOfWeekElement.textContent = localizedDays[currentDate.getDay()];
    }
}

/**
 * Initialize the appointment form with data from server
 */
function initializeAppointmentForm(data) {
    appointmentsData = data.appointments || [];
    today = data.today; // Actual server date - never changes

    // Parse date as local time, not UTC
    const [year, month, day] = data.today.split('-');
    currentDate = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));

    barbersData = data.barbers || [];
    barberProcedureMap = data.barberProcedureMap || {};

    // Update day of week display after currentDate is set
    updateDayOfWeek();

    // Update hidden input for form submission
    document.getElementById('selected_appointment_date').value = today;

    // Load availability for today on initial load
    loadAvailability(today);
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

    // Format date as yyyy-mm-dd using local time, not UTC
    const year = newDate.getFullYear();
    const month = String(newDate.getMonth() + 1).padStart(2, '0');
    const day = String(newDate.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;

    // Update current date
    currentDate = newDate;

    // Update day of week display
    updateDayOfWeek();

    // Update hidden input for form submission
    document.getElementById('selected_appointment_date').value = dateStr;

    // Update URL without reloading page
    const url = new URL(window.location);
    url.searchParams.set('date', dateStr);

    // Preserve selected procedure in URL
    if (selectedProcedure && selectedProcedure.id) {
        url.searchParams.set('procedure', selectedProcedure.id);
    }

    window.history.pushState({}, '', url);

    // Update datepicker display (convert to dd-MM-yyyy format)
    const calendarInput = $('#calendar_display');
    if (calendarInput.length) {
        calendarInput.val(formatDateForDisplay(dateStr));
    }

    // Load availability via AJAX
    loadAvailability(dateStr);

    // Update date navigation buttons
    updateDateNavigationButtons();
}

/**
 * Load availability data via AJAX (occupied slots + working hours)
 */
function loadAvailability(date) {
    fetch('/appointment/api/availability/' + date)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            occupiedSlots = data.occupiedSlots || {};
            barberWorkingHours = data.barberWorkingHours || {};
            renderBarbers();
        })
        .catch(error => {
            console.error('Error loading availability:', error);
        });
}

/**
 * Render barber sections with time slots
 */
function renderBarbers() {
    const container = document.getElementById('barbers-container');
    if (!container) {
        console.error('Container #barbers-container not found!');
        return;
    }
    container.innerHTML = '';

    // Get current date/time for isPast check
    const now = new Date();
    const selectedDateObj = new Date(currentDate);

    barbersData.forEach(barber => {
        const barberId = barber.id;
        const barberSlots = occupiedSlots[barberId] || [];
        const isFullDayOff = barberSlots.includes('__FULL_DAY_OFF__');
        const workingHours = barberWorkingHours[barberId];

        // Skip if barber is off or has no working hours
        if (isFullDayOff || !workingHours) {
            return;
        }

        // Create barber section
        const barberSection = document.createElement('div');
        barberSection.className = 'barber-section';
        barberSection.dataset.barberId = barberId;
        barberSection.dataset.barberJunior = barber.isBarberJunior ? 'true' : 'false';

        // Barber header
        const header = document.createElement('h3');
        header.className = 'barber-name';
        header.innerHTML = `
            ${translateRole(barber.barberRole)} ${barber.firstName} ${barber.lastName}
            <small class="text-muted">(${workingHours.start} - ${workingHours.end})</small>
        `;
        barberSection.appendChild(header);

        // Time slots container
        const slotsContainer = document.createElement('div');
        slotsContainer.className = 'time-slots-horizontal';

        // Generate time slots
        const [startHour, startMin] = workingHours.start.split(':').map(Number);
        const [endHour, endMin] = workingHours.end.split(':').map(Number);
        const startMinutes = startHour * 60 + startMin;
        const endMinutes = endHour * 60 + endMin;

        for (let minutes = startMinutes; minutes < endMinutes; minutes += 30) {
            const slotHour = Math.floor(minutes / 60);
            const slotMin = minutes % 60;
            const timeSlot = `${String(slotHour).padStart(2, '0')}:${String(slotMin).padStart(2, '0')}`;

            const isOccupied = barberSlots.includes(timeSlot);
            const isExcluded = workingHours.excludedSlots && workingHours.excludedSlots.includes(timeSlot);

            // Check if slot is in the past
            const slotDateTime = new Date(selectedDateObj);
            slotDateTime.setHours(slotHour, slotMin, 0, 0);
            const isPast = slotDateTime < now;

            const isDisabled = isOccupied || isExcluded || isPast;

            // Create slot button
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `time-slot-box ${isDisabled ? 'booked' : 'available'}`;
            button.dataset.time = timeSlot;
            button.dataset.barberId = barberId;
            button.dataset.occupied = isDisabled ? 'true' : 'false';
            button.dataset.minutes = minutes;
            button.disabled = isDisabled;
            button.onclick = function() {
                selectTimeSlot(this, barberId, timeSlot);
            };

            button.innerHTML = `
                <div class="time-label">${timeSlot}</div>
                <div class="time-icon"><i class="fas fa-cut"></i></div>
            `;

            slotsContainer.appendChild(button);
        }

        barberSection.appendChild(slotsContainer);
        container.appendChild(barberSection);
    });

    // Apply procedure filtering if procedure is selected
    if (selectedProcedure) {
        filterBarbersByProcedure(parseInt(selectedProcedure.id));
    }
}

// updateTimeSlots() function removed - replaced by renderBarbers()

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

        if (calendarInput.length && typeof calendarInput.datepicker === 'function') {
            try {
                // Initialize datepicker
                calendarInput.datepicker({
                    format: 'dd-mm-yyyy',
                    minDate: formatDateForDisplay(today),
                    maxDate: maxDate,
                    uiLibrary: 'bootstrap4',
                    showRightIcon: false,
                    weekStartDay: 1, // Start week on Monday
                    change: function (e) {
                        const selectedDateDisplay = e.target.value; // dd-mm-yyyy format

                        if (selectedDateDisplay) {
                            // Convert from dd-mm-yyyy to yyyy-mm-dd for internal use
                            const selectedDate = formatDateForAPI(selectedDateDisplay);

                            // Update current date - parse as local time, not UTC
                            const [year, month, day] = selectedDate.split('-');
                            currentDate = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));

                            // Update day of week display
                            updateDayOfWeek();

                            // Update hidden input for form submission
                            document.getElementById('selected_appointment_date').value = selectedDate;

                            // Update URL without reloading page (use yyyy-mm-dd format)
                            const url = new URL(window.location);
                            url.searchParams.set('date', selectedDate);

                            // Preserve selected procedure in URL
                            if (selectedProcedure && selectedProcedure.id) {
                                url.searchParams.set('procedure', selectedProcedure.id);
                            }

                            window.history.pushState({}, '', url);

                            // Load availability via AJAX (use yyyy-mm-dd format)
                            loadAvailability(selectedDate);

                            // Update date navigation buttons
                            updateDateNavigationButtons();
                        }
                    }
                });

                // Simple click handler - let Gijgo handle the opening
                if (calendarInputElement) {
                    // Prevent keyboard input only
                    calendarInputElement.addEventListener('keydown', function(e) {
                        e.preventDefault();
                        return false;
                    });
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
