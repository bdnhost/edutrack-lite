<?php
/**
 * Admin Courses Page
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_edutrack_courses')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
?>

<div class="wrap edutrack-admin">
    <h1 class="wp-heading-inline">
        <i class="bi bi-book"></i>
        <?php echo esc_html__('ניהול קורסים', 'edutrack-lite'); ?>
    </h1>
    <a href="#" class="page-title-action" id="btn-new-course">
        <i class="bi bi-plus-circle"></i> הוסף קורס חדש
    </a>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="courses-container">
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

<!-- New Course Modal -->
<div class="modal fade" id="newCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">קורס חדש</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="new-course-form">
                    <div class="mb-3">
                        <label class="form-label">שם הקורס *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">קוד קורס</label>
                        <input type="text" class="form-control" name="code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">מכללה *</label>
                        <select class="form-select" name="institution_id" required>
                            <option value="">בחר מכללה...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">סמסטר *</label>
                        <input type="text" class="form-control" name="semester" placeholder="לדוגמה: חורף 2024" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-save-course">שמור</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let modal = null;

    // Initialize
    loadInstitutions();
    loadCourses();

    $('#btn-new-course').click(function(e) {
        e.preventDefault();
        showNewCourseModal();
    });

    $('#btn-save-course').click(function() {
        saveCourse();
    });

    function loadInstitutions() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_institutions',
                nonce: edutrackAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    let select = $('select[name="institution_id"]');
                    response.data.forEach(function(inst) {
                        select.append('<option value="' + inst.id + '">' + escapeHtml(inst.name) + '</option>');
                    });
                }
            }
        });
    }

    function loadCourses() {
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
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('שגיאה בטעינת הקורסים');
            }
        });
    }

    function displayCourses(courses) {
        if (courses.length === 0) {
            $('#courses-container').html('<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> אין קורסים עדיין. צור את הקורס הראשון שלך!</div>');
            return;
        }

        let html = '<div class="row">';
        courses.forEach(function(course) {
            html += '<div class="col-md-6 col-lg-4 mb-4">';
            html += '<div class="card h-100">';
            html += '<div class="card-body">';
            html += '<h5 class="card-title">' + escapeHtml(course.name) + '</h5>';
            if (course.code) {
                html += '<p class="card-text text-muted">קוד: ' + escapeHtml(course.code) + '</p>';
            }
            html += '<p class="card-text">';
            html += '<i class="bi bi-building"></i> ' + escapeHtml(course.institution_name || '') + '<br>';
            html += '<i class="bi bi-calendar"></i> ' + escapeHtml(course.semester) + '<br>';
            html += '<i class="bi bi-people"></i> ' + course.student_count + ' תלמידים<br>';
            html += '<i class="bi bi-journal-check"></i> ' + course.session_count + ' שיעורים';
            html += '</p>';
            html += '</div>';
            html += '<div class="card-footer">';
            html += '<a href="' + edutrackAdmin.siteUrl + '/wp-admin/admin.php?page=edutrack-course-detail&course_id=' + course.id + '" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> צפה</a> ';
            html += '<button class="btn btn-sm btn-danger btn-delete-course" data-id="' + course.id + '"><i class="bi bi-trash"></i> מחק</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';

        $('#courses-container').html(html);

        // Delete handlers
        $('.btn-delete-course').click(function() {
            if (confirm('האם אתה בטוח שברצונך למחוק קורס זה?')) {
                deleteCourse($(this).data('id'));
            }
        });
    }

    function showNewCourseModal() {
        $('#new-course-form')[0].reset();
        modal = new bootstrap.Modal($('#newCourseModal'));
        modal.show();
    }

    function saveCourse() {
        let formData = {
            action: 'edutrack_create_course',
            nonce: edutrackAdmin.nonce,
            name: $('input[name="name"]').val(),
            code: $('input[name="code"]').val(),
            institution_id: $('select[name="institution_id"]').val(),
            semester: $('input[name="semester"]').val()
        };

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    modal.hide();
                    loadCourses();
                    showSuccess('הקורס נוצר בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('שגיאה ביצירת הקורס');
            }
        });
    }

    function deleteCourse(courseId) {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_delete_course',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    loadCourses();
                    showSuccess('הקורס נמחק בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('שגיאה במחיקת הקורס');
            }
        });
    }

    function showError(message) {
        $('#courses-container').html('<div class="alert alert-danger">' + escapeHtml(message) + '</div>');
    }

    function showSuccess(message) {
        // Simple success notification
        let notification = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
            escapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        $('body').append(notification);
        setTimeout(function() {
            notification.remove();
        }, 3000);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
