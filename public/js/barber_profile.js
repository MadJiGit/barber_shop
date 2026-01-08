/**
 * Barber Profile Page JavaScript
 * Handles tab activation from URL parameters
 */

$(document).ready(function() {
    // Activate correct tab from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'calendar'; // Default to calendar tab

    // Direct class manipulation (more reliable than Bootstrap API on page load)
    // Remove active from all tabs
    $('.nav-link').removeClass('active');
    $('.tab-pane').removeClass('show active');

    // Activate selected tab
    $('#' + tab + '-tab').addClass('active');
    $('#' + tab).addClass('show active');
});
