<?php
/**
 * Public Template - Lecturer Dashboard
 * Usage: [edutrack_dashboard]
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="edutrack-dashboard">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-grid-3x3-gap"></i>
                הדשבורד שלי
            </h4>
        </div>
        <div class="card-body">
            <p>זוהי תצוגת Dashboard בעמוד ציבורי. למנהל מלא, בקר ב<a href="<?php echo admin_url('admin.php?page=edutrack'); ?>">ממשק הניהול</a>.</p>

            <div id="courses-list-public">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">טוען...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    loadCourses();

    function loadCourses() {
        $.ajax({
            url: edutrackPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_courses',
                nonce: edutrackPublic.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayCourses(response.data);
                } else {
                    $('#courses-list-public').html('<div class="alert alert-info">אין קורסים עדיין.</div>');
                }
            },
            error: function() {
                $('#courses-list-public').html('<div class="alert alert-danger">שגיאה בטעינת הקורסים</div>');
            }
        });
    }

    function displayCourses(courses) {
        if (courses.length === 0) {
            $('#courses-list-public').html('<div class="alert alert-info">אין קורסים עדיין.</div>');
            return;
        }

        let html = '<div class="list-group">';
        courses.forEach(function(course) {
            html += '<div class="list-group-item">';
            html += '<div class="d-flex w-100 justify-content-between">';
            html += '<h5 class="mb-1">' + escapeHtml(course.name) + '</h5>';
            html += '<small>' + escapeHtml(course.semester) + '</small>';
            html += '</div>';
            html += '<p class="mb-1">' + escapeHtml(course.institution_name) + '</p>';
            html += '<small>' + course.student_count + ' תלמידים | ' + course.session_count + ' שיעורים</small>';
            html += '</div>';
        });
        html += '</div>';

        $('#courses-list-public').html(html);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
