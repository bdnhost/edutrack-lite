<?php
/**
 * Public Template - Student Attendance Marking
 * Usage: [edutrack_attend] or [edutrack_attend token="xyz"]
 */

if (!defined('ABSPATH')) {
    exit;
}

$token = isset($token) ? $token : '';
?>

<div class="edutrack-attend-container">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">
                <i class="bi bi-check-circle"></i>
                רישום נוכחות
            </h4>
        </div>
        <div class="card-body" id="attend-form-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">אמת שיעור...</span>
                </div>
                <p class="mt-3">מאמת פרטי השיעור...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const token = '<?php echo esc_js($token); ?>';

    if (!token) {
        showError('קוד נוכחות לא תקין. אנא סרוק את ה-QR Code שוב.');
        return;
    }

    // Verify session
    verifySession();

    function verifySession() {
        $.ajax({
            url: edutrackPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_verify_session',
                token: token
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.is_expired) {
                        showError('השיעור פג תוקף. QR Code כבר לא פעיל.');
                    } else {
                        showForm(response.data.session);
                    }
                } else {
                    showError(response.data.message || 'שיעור לא נמצא');
                }
            },
            error: function() {
                showError('שגיאה בחיבור לשרת');
            }
        });
    }

    function showForm(session) {
        let html = '<div class="text-center mb-4">';
        html += '<h5>' + escapeHtml(session.course_name) + '</h5>';
        html += '<p class="text-muted">סרקת בהצלחה!</p>';
        html += '</div>';

        html += '<form id="attendance-form">';
        html += '<div class="mb-3">';
        html += '<label for="phone" class="form-label">מספר טלפון</label>';
        html += '<input type="tel" class="form-control form-control-lg text-center" id="phone" placeholder="05XXXXXXXX" required maxlength="10">';
        html += '<div class="form-text">הזן את מספר הטלפון שלך (10 ספרות)</div>';
        html += '</div>';
        html += '<button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-check-circle"></i> אשר נוכחות</button>';
        html += '</form>';

        html += '<div id="result-message" class="mt-3"></div>';

        $('#attend-form-container').html(html);

        // Form submit
        $('#attendance-form').submit(function(e) {
            e.preventDefault();
            markAttendance();
        });
    }

    function markAttendance() {
        let phone = $('#phone').val().trim();

        if (!phone || phone.length !== 10) {
            showMessage('נא להזין מספר טלפון תקין (10 ספרות)', 'warning');
            return;
        }

        $('#attendance-form button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> שולח...');

        $.ajax({
            url: edutrackPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_mark_attendance',
                nonce: edutrackPublic.nonce,
                token: token,
                phone: phone
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message, response.data.student_name);
                } else {
                    showMessage(response.data.message || 'שגיאה ברישום נוכחות', 'danger');
                    $('#attendance-form button').prop('disabled', false).html('<i class="bi bi-check-circle"></i> אשר נוכחות');
                }
            },
            error: function() {
                showMessage('שגיאה בחיבור לשרת', 'danger');
                $('#attendance-form button').prop('disabled', false).html('<i class="bi bi-check-circle"></i> אשר נוכחות');
            }
        });
    }

    function showSuccess(message, studentName) {
        let html = '<div class="text-center py-4">';
        html += '<div class="text-success mb-3">';
        html += '<i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>';
        html += '</div>';
        html += '<h4 class="text-success">נוכחות נרשמה!</h4>';
        html += '<p class="lead">' + escapeHtml(studentName) + '</p>';
        html += '<p class="text-muted">' + escapeHtml(message) + '</p>';
        html += '</div>';

        $('#attend-form-container').html(html);
    }

    function showMessage(message, type) {
        let html = '<div class="alert alert-' + type + ' alert-dismissible fade show">';
        html += escapeHtml(message);
        html += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        html += '</div>';
        $('#result-message').html(html);
    }

    function showError(message) {
        let html = '<div class="alert alert-danger text-center">';
        html += '<i class="bi bi-exclamation-triangle-fill"></i><br>';
        html += escapeHtml(message);
        html += '</div>';
        $('#attend-form-container').html(html);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
