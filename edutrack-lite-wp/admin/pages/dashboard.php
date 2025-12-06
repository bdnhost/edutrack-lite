<?php
/**
 * Admin Dashboard Page
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_edutrack')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$user = wp_get_current_user();
?>

<div class="wrap edutrack-admin">
    <h1 class="wp-heading-inline">
        <i class="bi bi-grid-3x3-gap"></i>
        <?php echo esc_html__('EduTrack Dashboard', 'edutrack-lite'); ?>
    </h1>

    <p class="edutrack-welcome">
        שלום <?php echo esc_html($user->display_name); ?>, ברוך הבא למערכת ניהול הנוכחות
    </p>

    <div class="row mt-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-book"></i> קורסים
                    </h5>
                    <p class="card-text display-6" id="total-courses">-</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-people"></i> תלמידים
                    </h5>
                    <p class="card-text display-6" id="total-students">-</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calendar-check"></i> שיעורים
                    </h5>
                    <p class="card-text display-6" id="total-sessions">-</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-circle"></i> נוכחויות
                    </h5>
                    <p class="card-text display-6" id="total-attendance">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Courses -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-book"></i> הקורסים שלי
                    </h5>
                    <a href="<?php echo admin_url('admin.php?page=edutrack-courses'); ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> קורס חדש
                    </a>
                </div>
                <div class="card-body">
                    <div id="courses-list" class="table-responsive">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">טוען...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load dashboard data
    loadDashboardData();

    function loadDashboardData() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_courses',
                nonce: edutrackAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayCourses(response.data);
                    updateStats(response.data);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('שגיאה בטעינת הנתונים');
            }
        });
    }

    function displayCourses(courses) {
        if (courses.length === 0) {
            $('#courses-list').html('<div class="alert alert-info">אין קורסים עדיין. <a href="<?php echo admin_url('admin.php?page=edutrack-courses'); ?>">צור קורס ראשון</a></div>');
            return;
        }

        let html = '<table class="table table-striped table-hover">';
        html += '<thead><tr>';
        html += '<th>שם הקורס</th>';
        html += '<th>מכללה</th>';
        html += '<th>סמסטר</th>';
        html += '<th>תלמידים</th>';
        html += '<th>שיעורים</th>';
        html += '<th>פעולות</th>';
        html += '</tr></thead><tbody>';

        courses.forEach(function(course) {
            html += '<tr>';
            html += '<td><strong>' + escapeHtml(course.name) + '</strong></td>';
            html += '<td>' + escapeHtml(course.institution_name || '') + '</td>';
            html += '<td>' + escapeHtml(course.semester) + '</td>';
            html += '<td><span class="badge bg-primary">' + course.student_count + '</span></td>';
            html += '<td><span class="badge bg-info">' + course.session_count + '</span></td>';
            html += '<td>';
            html += '<a href="<?php echo admin_url('admin.php?page=edutrack-course-detail&course_id='); ?>' + course.id + '" class="btn btn-sm btn-primary">צפה</a>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#courses-list').html(html);
    }

    function updateStats(courses) {
        let totalStudents = 0;
        let totalSessions = 0;

        courses.forEach(function(course) {
            totalStudents += parseInt(course.student_count) || 0;
            totalSessions += parseInt(course.session_count) || 0;
        });

        $('#total-courses').text(courses.length);
        $('#total-students').text(totalStudents);
        $('#total-sessions').text(totalSessions);
        // Total attendance would need separate query
    }

    function showError(message) {
        $('#courses-list').html('<div class="alert alert-danger">' + escapeHtml(message) + '</div>');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
