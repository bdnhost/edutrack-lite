<?php
/**
 * Admin Session View Page - Active Session with QR Code and Real-time Attendance
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_edutrack_attendance')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if (!$session_id) {
    echo '<div class="wrap"><h1>שגיאה</h1><p>מזהה שיעור לא תקין.</p></div>';
    return;
}
?>

<div class="wrap edutrack-admin edutrack-session-view">
    <div class="edutrack-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="#" class="btn btn-sm btn-outline-secondary" id="btn-back-to-course">
                    <i class="bi bi-arrow-right"></i> חזרה לקורס
                </a>
                <h1 class="wp-heading-inline mt-2">
                    <i class="bi bi-broadcast"></i>
                    <span id="session-title">שיעור פעיל</span>
                </h1>
            </div>
            <div>
                <button class="btn btn-danger" id="btn-close-session">
                    <i class="bi bi-stop-circle"></i> סגור שיעור
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- QR Code Section -->
        <div class="col-lg-5">
            <div class="card session-qr-card">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code לסריקה</h5>
                </div>
                <div class="card-body text-center">
                    <div id="qrcode-container" class="mb-3">
                        <div class="spinner-border text-primary"></div>
                    </div>

                    <div id="session-info" class="mt-3">
                        <!-- Session info will be loaded here -->
                    </div>

                    <div class="alert alert-info mt-3" id="session-instructions">
                        <strong><i class="bi bi-info-circle"></i> הוראות:</strong><br>
                        1. הקרן את ה-QR Code על המסך<br>
                        2. התלמידים סורקים עם המכשיר הנייד<br>
                        3. הם מזינים מספר טלפון ומאשרים<br>
                        4. הרישום יופיע כאן בזמן אמת
                    </div>

                    <!-- Timer -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6><i class="bi bi-clock"></i> זמן נותר:</h6>
                            <h3 id="session-timer" class="text-primary mb-0">--:--</h3>
                        </div>
                    </div>

                    <!-- Link -->
                    <div class="mt-3">
                        <small class="text-muted">קישור ישיר:</small><br>
                        <input type="text" class="form-control form-control-sm mt-1" id="session-link" readonly>
                        <button class="btn btn-sm btn-secondary mt-2" id="btn-copy-link">
                            <i class="bi bi-clipboard"></i> העתק קישור
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance List Section -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i>
                        רשימת נוכחות (<span id="attendance-count">0</span>)
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-success" id="btn-refresh">
                            <i class="bi bi-arrow-clockwise"></i> רענן
                        </button>
                        <button class="btn btn-sm btn-info" id="btn-export-attendance">
                            <i class="bi bi-download"></i> ייצוא
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="mb-3">
                        <input type="text" class="form-control" id="search-attendance" placeholder="חיפוש תלמיד...">
                    </div>

                    <!-- Attendance List -->
                    <div id="attendance-list" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">טוען נוכחות...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> סטטיסטיקות</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-box">
                                <i class="bi bi-people fs-3 text-primary"></i>
                                <h4 id="total-students">-</h4>
                                <small class="text-muted">סה"כ תלמידים</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <i class="bi bi-check-circle fs-3 text-success"></i>
                                <h4 id="attended-count">-</h4>
                                <small class="text-muted">נרשמו</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <i class="bi bi-percent fs-3 text-info"></i>
                                <h4 id="attendance-percentage">-</h4>
                                <small class="text-muted">אחוז נוכחות</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.session-qr-card #qrcode-container {
    padding: 30px;
    background: white;
    border-radius: 10px;
    display: inline-block;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.session-qr-card #qrcode-container img {
    display: block;
    margin: 0 auto;
    max-width: 100%;
    height: auto;
}

.stat-box {
    padding: 15px;
}

.stat-box h4 {
    margin: 10px 0 5px 0;
    font-weight: bold;
}

.attendance-item {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.attendance-item:hover {
    background-color: #f8f9fa;
}

.attendance-item.new {
    animation: slideIn 0.3s ease-out;
    background-color: #d1ecf1;
}

@keyframes slideIn {
    from {
        transform: translateX(-20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.student-info strong {
    font-size: 1.1em;
}

.attendance-time {
    color: #6c757d;
    font-size: 0.85em;
}
</style>

<script>
jQuery(document).ready(function($) {
    const sessionId = <?php echo $session_id; ?>;
    let session = null;
    let attendanceRecords = [];
    let totalStudents = 0;
    let refreshInterval = null;
    let timerInterval = null;

    // Initialize
    loadSessionData();
    startAutoRefresh();

    // Button handlers
    $('#btn-close-session').click(closeSession);
    $('#btn-refresh').click(loadAttendance);
    $('#btn-copy-link').click(copyLink);
    $('#btn-export-attendance').click(exportAttendance);
    $('#btn-back-to-course').click(backToCourse);

    // Search
    $('#search-attendance').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('.attendance-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(search) > -1);
        });
    });

    // Load session data
    function loadSessionData() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_session_status',
                nonce: edutrackAdmin.nonce,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    session = response.data.session;
                    totalStudents = response.data.total_students || 0;
                    displaySession();
                    loadAttendance();
                    startTimer();
                } else {
                    showError('שגיאה בטעינת השיעור: ' + response.data.message);
                }
            },
            error: function() {
                showError('שגיאה בחיבור לשרת');
            }
        });
    }

    function displaySession() {
        // Update header
        $('#session-title').text('שיעור #' + session.id);

        // Generate QR Code
        const attendUrl = edutrackAdmin.siteUrl + '/?edutrack_attend=' + session.token;
        $('#session-link').val(attendUrl);

        $('#qrcode-container').empty();
        new QRCode(document.getElementById('qrcode-container'), {
            text: attendUrl,
            width: 280,
            height: 280,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        // Session info
        let info = '<p class="mb-1"><strong>תאריך:</strong> ' + formatDate(session.started_at) + '</p>';
        info += '<p class="mb-1"><strong>שעת התחלה:</strong> ' + formatTime(session.started_at) + '</p>';
        info += '<p class="mb-0"><strong>סטטוס:</strong> ';
        info += session.status === 'active' ?
            '<span class="badge bg-success">פעיל</span>' :
            '<span class="badge bg-secondary">סגור</span>';
        info += '</p>';
        $('#session-info').html(info);

        // Update stats
        $('#total-students').text(totalStudents);

        // Check if expired
        if (session.status === 'closed' || isExpired()) {
            clearInterval(refreshInterval);
            clearInterval(timerInterval);
            $('#btn-close-session').prop('disabled', true);
            $('#session-instructions').removeClass('alert-info').addClass('alert-warning')
                .html('<strong><i class="bi bi-exclamation-triangle"></i> השיעור הסתיים</strong>');
        }
    }

    function loadAttendance() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_session_status',
                nonce: edutrackAdmin.nonce,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    const newRecords = response.data.records || [];
                    displayAttendance(newRecords);
                    updateStats(newRecords.length);
                }
            }
        });
    }

    function displayAttendance(records) {
        if (records.length === 0) {
            $('#attendance-list').html('<div class="text-center py-4 text-muted">' +
                '<i class="bi bi-inbox fs-1"></i><br>' +
                'אין נוכחות עדיין. ממתין לתלמידים...' +
                '</div>');
            return;
        }

        // Find new records
        const existingIds = attendanceRecords.map(r => r.id);
        const newRecordIds = records.filter(r => !existingIds.includes(r.id)).map(r => r.id);

        attendanceRecords = records;

        let html = '';
        records.forEach(record => {
            const isNew = newRecordIds.includes(record.id);
            html += '<div class="attendance-item' + (isNew ? ' new' : '') + '">';
            html += '<div class="student-info">';
            html += '<strong>' + escapeHtml(record.first_name + ' ' + record.last_name) + '</strong><br>';
            html += '<small class="text-muted">' + escapeHtml(record.phone) + '</small>';
            html += '</div>';
            html += '<div class="attendance-time">';
            html += '<i class="bi bi-check-circle-fill text-success"></i><br>';
            html += '<small>' + formatTime(record.timestamp) + '</small>';
            html += '</div>';
            html += '</div>';
        });

        $('#attendance-list').html(html);

        // Play sound for new attendance (optional)
        if (newRecordIds.length > 0) {
            // You can add a sound notification here
            console.log('New attendance:', newRecordIds.length);
        }
    }

    function updateStats(attendedCount) {
        $('#attendance-count').text(attendedCount);
        $('#attended-count').text(attendedCount);

        const percentage = totalStudents > 0 ?
            Math.round((attendedCount / totalStudents) * 100) : 0;
        $('#attendance-percentage').text(percentage + '%');
    }

    function startTimer() {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        const now = new Date().getTime();
        const expiresAt = new Date(session.expires_at).getTime();
        const distance = expiresAt - now;

        if (distance < 0) {
            $('#session-timer').text('פג תוקף').addClass('text-danger');
            clearInterval(timerInterval);
            $('#session-instructions').removeClass('alert-info').addClass('alert-warning')
                .html('<strong><i class="bi bi-clock-history"></i> השיעור פג תוקף</strong>');
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        $('#session-timer').text(
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );

        // Warning color when less than 5 minutes
        if (minutes < 5) {
            $('#session-timer').removeClass('text-primary').addClass('text-warning');
        }
    }

    function startAutoRefresh() {
        refreshInterval = setInterval(loadAttendance, 3000); // Refresh every 3 seconds
    }

    function closeSession() {
        if (!confirm('האם אתה בטוח שברצונך לסגור את השיעור? לא ניתן יהיה להוסיף נוכחות נוספת.')) {
            return;
        }

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_close_session',
                nonce: edutrackAdmin.nonce,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('השיעור נסגר בהצלחה');
                    setTimeout(backToCourse, 1500);
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function copyLink() {
        const link = $('#session-link');
        link.select();
        document.execCommand('copy');
        showSuccess('הקישור הועתק ללוח');
    }

    function exportAttendance() {
        window.location.href = edutrackAdmin.ajaxUrl +
            '?action=edutrack_export_session_attendance' +
            '&session_id=' + sessionId +
            '&nonce=' + edutrackAdmin.nonce;
    }

    function backToCourse() {
        if (session && session.course_id) {
            window.location.href = edutrackAdmin.siteUrl +
                '/wp-admin/admin.php?page=edutrack-course-detail&course_id=' + session.course_id;
        } else {
            window.location.href = edutrackAdmin.siteUrl +
                '/wp-admin/admin.php?page=edutrack-courses';
        }
    }

    function isExpired() {
        const now = new Date().getTime();
        const expiresAt = new Date(session.expires_at).getTime();
        return now > expiresAt;
    }

    // Utility functions
    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }

    function formatDate(datetime) {
        const date = new Date(datetime);
        return date.toLocaleDateString('he-IL');
    }

    function formatTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleTimeString('he-IL', {hour: '2-digit', minute: '2-digit'});
    }

    function showSuccess(message) {
        const notification = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
            escapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        $('body').append(notification);
        setTimeout(() => notification.fadeOut(() => notification.remove()), 2000);
    }

    function showError(message) {
        alert(message);
    }

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (refreshInterval) clearInterval(refreshInterval);
        if (timerInterval) clearInterval(timerInterval);
    });
});
</script>
