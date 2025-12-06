<?php
/**
 * Public Template - Session with QR Code
 * Usage: [edutrack_session id="123"]
 */

if (!defined('ABSPATH')) {
    exit;
}

$session_id = isset($session_id) ? intval($session_id) : 0;
?>

<div class="edutrack-session">
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">
                <i class="bi bi-qr-code"></i>
                שיעור פעיל
            </h4>
        </div>
        <div class="card-body text-center" id="session-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">טוען...</span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const sessionId = <?php echo $session_id; ?>;

    if (!sessionId) {
        $('#session-content').html('<div class="alert alert-danger">מזהה שיעור לא תקין</div>');
        return;
    }

    loadSession();
    let refreshInterval = setInterval(loadSession, 5000); // Refresh every 5 seconds

    function loadSession() {
        $.ajax({
            url: edutrackPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_session_status',
                nonce: edutrackPublic.nonce,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    displaySession(response.data);
                } else {
                    clearInterval(refreshInterval);
                    $('#session-content').html('<div class="alert alert-danger">' + escapeHtml(response.data.message) + '</div>');
                }
            },
            error: function() {
                clearInterval(refreshInterval);
                $('#session-content').html('<div class="alert alert-danger">שגיאה בטעינת השיעור</div>');
            }
        });
    }

    function displaySession(data) {
        if (data.session.status === 'closed' || data.is_expired) {
            clearInterval(refreshInterval);
            $('#session-content').html('<div class="alert alert-warning"><i class="bi bi-clock-history"></i> השיעור נסגר או פג תוקף</div>');
            return;
        }

        let html = '<div id="qrcode-container" class="mb-4"></div>';
        html += '<h5>סרוק QR Code כדי לרשום נוכחות</h5>';
        html += '<p class="text-muted">התלמידים נרשמו: <strong>' + data.count + '</strong></p>';

        if (data.records && data.records.length > 0) {
            html += '<hr>';
            html += '<h6>רשימת נוכחות:</h6>';
            html += '<div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">';
            data.records.forEach(function(record) {
                html += '<div class="list-group-item">';
                html += '<i class="bi bi-check-circle-fill text-success"></i> ';
                html += escapeHtml(record.first_name) + ' ' + escapeHtml(record.last_name);
                html += '</div>';
            });
            html += '</div>';
        }

        $('#session-content').html(html);

        // Generate QR Code
        let attendUrl = edutrackPublic.siteUrl + '/?edutrack_attend=' + data.session.token;
        $('#qrcode-container').empty();
        new QRCode(document.getElementById('qrcode-container'), {
            text: attendUrl,
            width: 300,
            height: 300
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
