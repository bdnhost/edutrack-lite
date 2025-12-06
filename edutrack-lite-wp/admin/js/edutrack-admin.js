/**
 * EduTrack Lite - Admin JavaScript
 */

(function($) {
    'use strict';

    // Global helper functions
    window.edutrackEscapeHtml = function(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    };

    window.edutrackShowNotification = function(message, type) {
        type = type || 'success';
        const notification = $('<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;">' +
            edutrackEscapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');

        $('body').append(notification);
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    };

    window.edutrackConfirm = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // Document ready
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Auto-dismiss alerts
        $('.alert.auto-dismiss').delay(3000).fadeOut();
    });

})(jQuery);
