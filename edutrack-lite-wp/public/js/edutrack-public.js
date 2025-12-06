/**
 * EduTrack Lite - Public JavaScript
 */

(function($) {
    'use strict';

    // Global helper functions
    window.edutrackEscapeHtml = function(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    };

    // Document ready
    $(document).ready(function() {
        // Phone number formatting
        $('input[type="tel"]').on('input', function() {
            // Remove non-numeric characters
            let value = $(this).val().replace(/\D/g, '');
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            $(this).val(value);
        });

        // Auto-focus on phone input
        $('input[type="tel"]').first().focus();
    });

})(jQuery);
