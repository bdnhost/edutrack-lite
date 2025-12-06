<?php
/**
 * Public Template - Course View
 * Usage: [edutrack_course id="123"]
 */

if (!defined('ABSPATH')) {
    exit;
}

$course_id = isset($course_id) ? intval($course_id) : 0;
?>

<div class="edutrack-course">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0" id="course-title">
                <i class="bi bi-book"></i>
                <span class="spinner-border spinner-border-sm"></span> טוען...
            </h4>
        </div>
        <div class="card-body" id="course-content">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">טוען פרטי קורס...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const courseId = <?php echo $course_id; ?>;

    if (!courseId) {
        $('#course-content').html('<div class="alert alert-danger">מזהה קורס לא תקין</div>');
        return;
    }

    loadCourse();

    function loadCourse() {
        $.ajax({
            url: edutrackPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_course',
                nonce: edutrackPublic.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    displayCourse(response.data);
                } else {
                    $('#course-content').html('<div class="alert alert-danger">' + escapeHtml(response.data.message) + '</div>');
                }
            },
            error: function() {
                $('#course-content').html('<div class="alert alert-danger">שגיאה בטעינת הקורס</div>');
            }
        });
    }

    function displayCourse(course) {
        $('#course-title').html('<i class="bi bi-book"></i> ' + escapeHtml(course.name));

        let html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<h6>פרטי הקורס</h6>';
        html += '<dl class="row">';
        html += '<dt class="col-sm-4">מכללה:</dt>';
        html += '<dd class="col-sm-8">' + escapeHtml(course.institution_name) + '</dd>';
        html += '<dt class="col-sm-4">סמסטר:</dt>';
        html += '<dd class="col-sm-8">' + escapeHtml(course.semester) + '</dd>';
        if (course.code) {
            html += '<dt class="col-sm-4">קוד:</dt>';
            html += '<dd class="col-sm-8">' + escapeHtml(course.code) + '</dd>';
        }
        html += '</dl>';
        html += '</div>';

        html += '<div class="col-md-6">';
        html += '<h6>סטטיסטיקות</h6>';
        html += '<dl class="row">';
        html += '<dt class="col-sm-6">תלמידים:</dt>';
        html += '<dd class="col-sm-6"><span class="badge bg-primary">' + course.student_count + '</span></dd>';
        html += '<dt class="col-sm-6">שיעורים:</dt>';
        html += '<dd class="col-sm-6"><span class="badge bg-info">' + course.session_count + '</span></dd>';
        html += '</dl>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<p class="text-center text-muted">למנהל מלא של הקורס, בקר ב<a href="' + edutrackPublic.siteUrl + '/wp-admin/admin.php?page=edutrack">ממשק הניהול</a>.</p>';

        $('#course-content').html(html);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
