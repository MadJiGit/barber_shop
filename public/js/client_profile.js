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

    // Tab persistence: Restore active tab from URL parameter
    restoreActiveTab();

    // Save active tab to URL when tab is clicked
    saveActiveTabOnClick();

    // Add tab parameter to form submissions
    addTabParameterToForms();
});

/**
 * Restore active tab from URL parameter
 */
function restoreActiveTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab) {
        // Deactivate all tabs
        document.querySelectorAll('#profileTabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });

        // Activate the specified tab
        const tabLink = document.querySelector(`#profileTabs a[href="#${activeTab}"]`);
        const tabPane = document.getElementById(activeTab);

        if (tabLink && tabPane) {
            tabLink.classList.add('active');
            tabPane.classList.add('show', 'active');
        }
    }
}

/**
 * Save active tab to URL when tab is clicked
 */
function saveActiveTabOnClick() {
    document.querySelectorAll('#profileTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            const targetTab = this.getAttribute('href').substring(1); // Remove '#'
            updateURLWithTab(targetTab);
        });
    });
}

/**
 * Update URL with tab parameter without reloading page
 */
function updateURLWithTab(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}

/**
 * Add tab parameter to form submissions to preserve active tab after page reload
 */
function addTabParameterToForms() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Find which tab is currently active
            const activeTab = document.querySelector('#profileTabs .nav-link.active');
            if (activeTab) {
                const tabName = activeTab.getAttribute('href').substring(1);

                // Add hidden input with tab parameter
                const tabInput = document.createElement('input');
                tabInput.type = 'hidden';
                tabInput.name = 'tab';
                tabInput.value = tabName;
                this.appendChild(tabInput);
            }
        });
    });
}

/**
 * Cancel appointment function
 * @param {number} appointmentId - The appointment ID to cancel
 * @param {string} csrfToken - CSRF token for security
 * @param {string} currentTab - Current active tab (appointments or history)
 */
function cancelAppointment(appointmentId, csrfToken, currentTab = 'appointments') {
    if (confirm('Сигурни ли сте, че искате да отмените това посещение?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/appointment/' + appointmentId + '/client-cancel';

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        // Add tab parameter to preserve active tab
        const tabInput = document.createElement('input');
        tabInput.type = 'hidden';
        tabInput.name = 'tab';
        tabInput.value = currentTab;
        form.appendChild(tabInput);

        document.body.appendChild(form);
        form.submit();
    }
}
