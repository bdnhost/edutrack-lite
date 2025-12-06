<?php
/**
 * Admin Course Detail Page - Full Management Interface
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_edutrack_courses')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if (!$course_id) {
    echo '<div class="wrap"><h1>שגיאה</h1><p>מזהה קורס לא תקין.</p></div>';
    return;
}
?>

<div class="wrap edutrack-admin edutrack-course-detail">
    <div class="edutrack-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?php echo admin_url('admin.php?page=edutrack-courses'); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right"></i> חזרה לקורסים
                </a>
                <h1 class="wp-heading-inline mt-2" id="course-name">
                    <i class="bi bi-book"></i>
                    <span class="spinner-border spinner-border-sm"></span> טוען...
                </h1>
            </div>
            <div id="course-actions">
                <!-- Course action buttons will be added here -->
            </div>
        </div>
    </div>

    <!-- Course Info Bar -->
    <div class="card mb-4" id="course-info-bar">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                    <h3 class="mb-0" id="student-count">-</h3>
                    <small class="text-muted">תלמידים</small>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                    <h3 class="mb-0" id="session-count">-</h3>
                    <small class="text-muted">שיעורים</small>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-check-circle fs-4 text-info"></i>
                    <h3 class="mb-0" id="attendance-rate">-</h3>
                    <small class="text-muted">ממוצע נוכחות</small>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-activity fs-4 text-warning"></i>
                    <h3 class="mb-0" id="active-session-indicator">-</h3>
                    <small class="text-muted">שיעור פעיל</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="courseTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                <i class="bi bi-info-circle"></i> סקירה
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button">
                <i class="bi bi-people"></i> תלמידים
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lessons-tab" data-bs-toggle="tab" data-bs-target="#lessons" type="button">
                <i class="bi bi-book"></i> מפגשים מתוכננים
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions" type="button">
                <i class="bi bi-calendar-event"></i> שיעורים
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button">
                <i class="bi bi-bar-chart"></i> דוחות
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="courseTabContent">

        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> פרטי הקורס</h5>
                        </div>
                        <div class="card-body" id="course-details">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning"></i> פעולות מהירות</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" id="btn-start-session">
                                    <i class="bi bi-play-circle"></i> התחל שיעור חדש
                                </button>
                                <button class="btn btn-success" id="btn-add-student">
                                    <i class="bi bi-person-plus"></i> הוסף תלמיד
                                </button>
                                <button class="btn btn-info" id="btn-import-students">
                                    <i class="bi bi-upload"></i> ייבוא תלמידים
                                </button>
                                <button class="btn btn-secondary" id="btn-edit-course">
                                    <i class="bi bi-pencil"></i> ערוך פרטי קורס
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Session Alert -->
                    <div class="card mt-3" id="active-session-card" style="display:none;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-broadcast"></i> שיעור פעיל</h5>
                        </div>
                        <div class="card-body text-center">
                            <p>יש שיעור פעיל כרגע</p>
                            <button class="btn btn-light btn-sm" id="btn-view-active-session">
                                <i class="bi bi-eye"></i> צפה בשיעור
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Tab -->
        <div class="tab-pane fade" id="students" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> רשימת תלמידים</h5>
                    <div>
                        <button class="btn btn-success btn-sm" id="btn-add-student-tab">
                            <i class="bi bi-person-plus"></i> הוסף תלמיד
                        </button>
                        <button class="btn btn-info btn-sm" id="btn-import-students-tab">
                            <i class="bi bi-upload"></i> ייבוא CSV
                        </button>
                        <button class="btn btn-secondary btn-sm" id="btn-export-students">
                            <i class="bi bi-download"></i> ייצוא
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="search-students" placeholder="חיפוש תלמיד...">
                    </div>
                    <div id="students-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons Tab -->
        <div class="tab-pane fade" id="lessons" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-book"></i> מפגשים מתוכננים</h5>
                    <div>
                        <button class="btn btn-success btn-sm" id="btn-add-lesson">
                            <i class="bi bi-plus-circle"></i> הוסף מפגש
                        </button>
                        <button class="btn btn-info btn-sm" id="btn-import-lessons">
                            <i class="bi bi-upload"></i> ייבוא מפגשים
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="lessons-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions Tab -->
        <div class="tab-pane fade" id="sessions" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> היסטוריית שיעורים</h5>
                    <button class="btn btn-primary btn-sm" id="btn-start-session-tab">
                        <i class="bi bi-play-circle"></i> התחל שיעור חדש
                    </button>
                </div>
                <div class="card-body">
                    <div id="sessions-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div class="tab-pane fade" id="reports" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> דוח נוכחות</h5>
                        </div>
                        <div class="card-body">
                            <div id="attendance-report">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> הוסף תלמיד חדש</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-student-form">
                    <div class="mb-3">
                        <label class="form-label">שם פרטי *</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">שם משפחה *</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">טלפון *</label>
                        <input type="tel" class="form-control" name="phone" placeholder="05XXXXXXXX" required maxlength="10">
                        <div class="form-text">10 ספרות, מתחיל ב-0</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">אימייל</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-save-student">שמור</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Students Modal -->
<div class="modal fade" id="importStudentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> ייבוא תלמידים מ-CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>פורמט הקובץ:</strong><br>
                    שורה ראשונה: כותרות (תתעלם)<br>
                    עמודות: שם פרטי, שם משפחה, טלפון, אימייל<br>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="btn-download-template">
                        <i class="bi bi-download"></i> הורד תבנית לדוגמה
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">העלה קובץ CSV</label>
                    <input type="file" class="form-control" id="csv-file" accept=".csv">
                </div>
                <div class="mb-3">
                    <label class="form-label">או הדבק תוכן CSV:</label>
                    <textarea class="form-control" id="csv-content" rows="8" placeholder="שם פרטי,שם משפחה,טלפון,אימייל
יוסי,כהן,0501234567,yossi@example.com
מיכל,לוי,0529876543,michal@example.com"></textarea>
                    <button type="button" class="btn btn-sm btn-info mt-2" id="btn-preview-csv">
                        <i class="bi bi-eye"></i> תצוגה מקדימה
                    </button>
                </div>
                <div id="import-preview" style="display:none;">
                    <h6><i class="bi bi-table"></i> תצוגה מקדימה:</h6>
                    <div id="preview-content"></div>
                    <div class="alert alert-warning mt-3" id="preview-warnings" style="display:none;">
                        <strong><i class="bi bi-exclamation-triangle"></i> אזהרות:</strong>
                        <ul id="warning-list"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-import-csv">ייבא</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> ערוך פרטי קורס</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-course-form">
                    <div class="mb-3">
                        <label class="form-label">שם הקורס *</label>
                        <input type="text" class="form-control" name="name" id="edit-course-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">קוד קורס</label>
                        <input type="text" class="form-control" name="code" id="edit-course-code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">מכללה *</label>
                        <select class="form-select" name="institution_id" id="edit-course-institution" required>
                            <option value="">בחר מכללה...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">סמסטר *</label>
                        <input type="text" class="form-control" name="semester" id="edit-course-semester" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-update-course">עדכן</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lesson Modal -->
<div class="modal fade" id="addLessonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> הוסף מפגש מתוכנן</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-lesson-form">
                    <div class="mb-3">
                        <label class="form-label">מספר מפגש *</label>
                        <input type="number" class="form-control" name="lesson_number" min="1" required>
                        <div class="form-text">מספר סידורי של המפגש (1, 2, 3...)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">כותרת *</label>
                        <input type="text" class="form-control" name="title" placeholder="נושא המפגש" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">תיאור</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="תיאור המפגש (אופציונלי)"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">תאריך מתוכנן *</label>
                            <input type="date" class="form-control" name="planned_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">שעה מתוכננת *</label>
                            <input type="time" class="form-control" name="planned_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">משך (דקות)</label>
                        <input type="number" class="form-control" name="duration" value="90" min="30" step="15">
                        <div class="form-text">ברירת מחדל: 90 דקות</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-save-lesson">שמור</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> ערוך מפגש</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-lesson-form">
                    <input type="hidden" id="edit-lesson-id">
                    <div class="mb-3">
                        <label class="form-label">מספר מפגש *</label>
                        <input type="number" class="form-control" id="edit-lesson-number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">כותרת *</label>
                        <input type="text" class="form-control" id="edit-lesson-title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">תיאור</label>
                        <textarea class="form-control" id="edit-lesson-description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">תאריך מתוכנן *</label>
                            <input type="date" class="form-control" id="edit-lesson-date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">שעה מתוכננת *</label>
                            <input type="time" class="form-control" id="edit-lesson-time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">משך (דקות)</label>
                        <input type="number" class="form-control" id="edit-lesson-duration" min="30" step="15">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-update-lesson">עדכן</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> ערוך פרטי תלמיד</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-student-form">
                    <input type="hidden" id="edit-student-id">
                    <div class="mb-3">
                        <label class="form-label">שם פרטי *</label>
                        <input type="text" class="form-control" id="edit-first-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">שם משפחה *</label>
                        <input type="text" class="form-control" id="edit-last-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">טלפון *</label>
                        <input type="tel" class="form-control" id="edit-phone" placeholder="05XXXXXXXX" required maxlength="10">
                        <div class="form-text">10 ספרות, מתחיל ב-0</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">אימייל</label>
                        <input type="email" class="form-control" id="edit-email">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                <button type="button" class="btn btn-primary" id="btn-update-student">עדכן</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const courseId = <?php echo $course_id; ?>;
    let currentCourse = null;
    let activeSession = null;
    let modals = {};

    // Initialize
    loadCourseData();
    loadInstitutions();

    // Initialize modals
    modals.addStudent = new bootstrap.Modal($('#addStudentModal'));
    modals.importStudents = new bootstrap.Modal($('#importStudentsModal'));
    modals.editCourse = new bootstrap.Modal($('#editCourseModal'));
    modals.editStudent = new bootstrap.Modal($('#editStudentModal'));
    modals.addLesson = new bootstrap.Modal($('#addLessonModal'));
    modals.editLesson = new bootstrap.Modal($('#editLessonModal'));

    // Tab change handlers
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');

        switch(target) {
            case '#students':
                loadStudents();
                break;
            case '#lessons':
                loadLessons();
                break;
            case '#sessions':
                loadSessions();
                break;
            case '#reports':
                loadAttendanceReport();
                break;
        }
    });

    // Button handlers
    $('#btn-start-session, #btn-start-session-tab').click(startNewSession);
    $('#btn-add-student, #btn-add-student-tab').click(() => modals.addStudent.show());
    $('#btn-import-students, #btn-import-students-tab').click(() => modals.importStudents.show());
    $('#btn-edit-course').click(showEditCourse);
    $('#btn-save-student').click(saveStudent);
    $('#btn-import-csv').click(importStudents);
    $('#btn-update-course').click(updateCourse);
    $('#btn-export-students').click(exportStudents);
    $('#btn-view-active-session').click(viewActiveSession);
    $('#btn-update-student').click(updateStudent);
    $('#btn-download-template').click(downloadCSVTemplate);
    $('#btn-preview-csv').click(previewCSV);
    $('#btn-add-lesson').click(() => modals.addLesson.show());
    $('#btn-save-lesson').click(saveLesson);
    $('#btn-update-lesson').click(updateLesson);

    // CSV file handler
    $('#csv-file').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#csv-content').val(e.target.result);
            };
            reader.readAsText(file);
        }
    });

    // Search students
    $('#search-students').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('#students-list table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(search) > -1);
        });
    });

    // Phone formatting
    $('input[type="tel"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) value = value.substring(0, 10);
        $(this).val(value);
    });

    // === LOAD FUNCTIONS ===

    function loadCourseData() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_course',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    currentCourse = response.data;
                    displayCourse(currentCourse);
                    checkActiveSession();
                } else {
                    showError('שגיאה בטעינת הקורס: ' + response.data.message);
                }
            },
            error: function() {
                showError('שגיאה בחיבור לשרת');
            }
        });
    }

    function displayCourse(course) {
        // Update header
        $('#course-name').html('<i class="bi bi-book"></i> ' + escapeHtml(course.name));

        // Update stats
        $('#student-count').text(course.student_count || 0);
        $('#session-count').text(course.session_count || 0);

        // Calculate attendance rate
        const rate = course.session_count > 0 ?
            Math.round((course.student_count * 100) / course.session_count) : 0;
        $('#attendance-rate').text(rate + '%');

        // Overview details
        let html = '<dl class="row">';
        html += '<dt class="col-sm-3">שם הקורס:</dt>';
        html += '<dd class="col-sm-9"><strong>' + escapeHtml(course.name) + '</strong></dd>';

        if (course.code) {
            html += '<dt class="col-sm-3">קוד:</dt>';
            html += '<dd class="col-sm-9">' + escapeHtml(course.code) + '</dd>';
        }

        html += '<dt class="col-sm-3">מכללה:</dt>';
        html += '<dd class="col-sm-9">' + escapeHtml(course.institution_name) + '</dd>';

        html += '<dt class="col-sm-3">סמסטר:</dt>';
        html += '<dd class="col-sm-9">' + escapeHtml(course.semester) + '</dd>';

        html += '<dt class="col-sm-3">נוצר בתאריך:</dt>';
        html += '<dd class="col-sm-9">' + formatDate(course.created_at) + '</dd>';

        html += '<dt class="col-sm-3">סטטוס:</dt>';
        html += '<dd class="col-sm-9"><span class="badge bg-success">פעיל</span></dd>';
        html += '</dl>';

        $('#course-details').html(html);
    }

    function checkActiveSession() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_active_session',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success && response.data) {
                    activeSession = response.data;
                    $('#active-session-indicator').html('<span class="badge bg-success">כן</span>');
                    $('#active-session-card').show();
                } else {
                    activeSession = null;
                    $('#active-session-indicator').html('<span class="badge bg-secondary">לא</span>');
                    $('#active-session-card').hide();
                }
            }
        });
    }

    function loadStudents() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_students',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    displayStudents(response.data);
                } else {
                    $('#students-list').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
            }
        });
    }

    function displayStudents(students) {
        if (students.length === 0) {
            $('#students-list').html('<div class="alert alert-info">אין תלמידים בקורס. הוסף תלמידים או ייבא מקובץ CSV.</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr>';
        html += '<th>#</th><th>שם מלא</th><th>טלפון</th><th>אימייל</th><th>פעולות</th>';
        html += '</tr></thead><tbody>';

        students.forEach((student, index) => {
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(student.first_name + ' ' + student.last_name) + '</strong></td>';
            html += '<td>' + escapeHtml(student.phone) + '</td>';
            html += '<td>' + escapeHtml(student.email || '-') + '</td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-info btn-edit-student me-1" data-student=\'' + JSON.stringify(student) + '\'>';
            html += '<i class="bi bi-pencil"></i></button>';
            html += '<button class="btn btn-sm btn-danger btn-delete-student" data-id="' + student.id + '">';
            html += '<i class="bi bi-trash"></i></button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#students-list').html(html);

        // Edit handlers
        $('.btn-edit-student').click(function() {
            const student = $(this).data('student');
            editStudent(student);
        });

        // Delete handlers
        $('.btn-delete-student').click(function() {
            if (confirm('האם אתה בטוח שברצונך למחוק תלמיד זה?')) {
                deleteStudent($(this).data('id'));
            }
        });
    }

    function loadSessions() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_course_sessions',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    displaySessions(response.data);
                } else {
                    $('#sessions-list').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
            }
        });
    }

    function displaySessions(sessions) {
        if (sessions.length === 0) {
            $('#sessions-list').html('<div class="alert alert-info">אין שיעורים עדיין. התחל שיעור ראשון!</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr>';
        html += '<th>#</th><th>תאריך</th><th>שעה</th><th>סטטוס</th><th>נוכחות</th><th>פעולות</th>';
        html += '</tr></thead><tbody>';

        sessions.forEach((session, index) => {
            const status = session.status === 'active' ?
                '<span class="badge bg-success">פעיל</span>' :
                '<span class="badge bg-secondary">נסגר</span>';

            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + formatDate(session.started_at) + '</td>';
            html += '<td>' + formatTime(session.started_at) + '</td>';
            html += '<td>' + status + '</td>';
            html += '<td><span class="badge bg-primary">' + (session.attendance_count || 0) + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-info btn-view-session" data-id="' + session.id + '">';
            html += '<i class="bi bi-eye"></i></button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#sessions-list').html(html);

        // View session handlers
        $('.btn-view-session').click(function() {
            viewSession($(this).data('id'));
        });
    }

    function loadAttendanceReport() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_attendance',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    displayAttendanceReport(response.data);
                } else {
                    $('#attendance-report').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
            }
        });
    }

    function displayAttendanceReport(data) {
        if (!data.sessions || data.sessions.length === 0) {
            $('#attendance-report').html('<div class="alert alert-info">אין נתוני נוכחות עדיין.</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
        html += '<thead><tr><th>תלמיד</th>';

        // Session headers
        data.sessions.forEach((session, index) => {
            html += '<th class="text-center">' + (index + 1) + '</th>';
        });
        html += '<th class="text-center">סה"כ</th>';
        html += '<th class="text-center">%</th>';
        html += '</tr></thead><tbody>';

        // Student rows
        data.attendance.forEach(row => {
            html += '<tr>';
            html += '<td><strong>' + escapeHtml(row.student.first_name + ' ' + row.student.last_name) + '</strong></td>';

            let total = 0;
            data.sessions.forEach(session => {
                const attended = row.sessions[session.id] || false;
                if (attended) total++;
                html += '<td class="text-center">';
                html += attended ? '<i class="bi bi-check-circle-fill text-success"></i>' :
                                  '<i class="bi bi-x-circle text-danger"></i>';
                html += '</td>';
            });

            const percentage = Math.round((total / data.sessions.length) * 100);
            html += '<td class="text-center"><strong>' + total + '</strong></td>';
            html += '<td class="text-center">';
            html += '<span class="badge ' + (percentage >= 80 ? 'bg-success' : percentage >= 60 ? 'bg-warning' : 'bg-danger') + '">';
            html += percentage + '%</span></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#attendance-report').html(html);
    }

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
                    let select = $('#edit-course-institution');
                    response.data.forEach(inst => {
                        select.append('<option value="' + inst.id + '">' + escapeHtml(inst.name) + '</option>');
                    });
                }
            }
        });
    }

    // === ACTION FUNCTIONS ===

    function startNewSession() {
        if (activeSession) {
            if (!confirm('יש כבר שיעור פעיל. האם לסגור אותו ולפתוח חדש?')) {
                return;
            }
            closeSession(activeSession.id, () => {
                createNewSession();
            });
        } else {
            createNewSession();
        }
    }

    function createNewSession() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_start_session',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('השיעור נפתח בהצלחה!');
                    activeSession = response.data.session;
                    viewActiveSession();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('שגיאה בפתיחת השיעור');
            }
        });
    }

    function closeSession(sessionId, callback) {
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
                    activeSession = null;
                    checkActiveSession();
                    if (callback) callback();
                }
            }
        });
    }

    function saveStudent() {
        const formData = {
            action: 'edutrack_add_student',
            nonce: edutrackAdmin.nonce,
            course_id: courseId,
            first_name: $('input[name="first_name"]').val(),
            last_name: $('input[name="last_name"]').val(),
            phone: $('input[name="phone"]').val(),
            email: $('input[name="email"]').val()
        };

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    modals.addStudent.hide();
                    $('#add-student-form')[0].reset();
                    loadStudents();
                    loadCourseData();
                    showSuccess('התלמיד נוסף בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function deleteStudent(studentId) {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_delete_student',
                nonce: edutrackAdmin.nonce,
                student_id: studentId
            },
            success: function(response) {
                if (response.success) {
                    loadStudents();
                    loadCourseData();
                    showSuccess('התלמיד נמחק בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function importStudents() {
        const csvData = $('#csv-content').val();
        if (!csvData) {
            alert('נא להזין או להעלות קובץ CSV');
            return;
        }

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_import_students',
                nonce: edutrackAdmin.nonce,
                course_id: courseId,
                csv_data: csvData
            },
            success: function(response) {
                if (response.success) {
                    modals.importStudents.hide();
                    $('#csv-content').val('');
                    $('#csv-file').val('');
                    loadStudents();
                    loadCourseData();

                    let msg = response.data.message;
                    if (response.data.errors && response.data.errors.length > 0) {
                        msg += '\n\nשגיאות:\n' + response.data.errors.join('\n');
                    }
                    alert(msg);
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function showEditCourse() {
        $('#edit-course-name').val(currentCourse.name);
        $('#edit-course-code').val(currentCourse.code || '');
        $('#edit-course-institution').val(currentCourse.institution_id);
        $('#edit-course-semester').val(currentCourse.semester);
        modals.editCourse.show();
    }

    function updateCourse() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_update_course',
                nonce: edutrackAdmin.nonce,
                course_id: courseId,
                name: $('#edit-course-name').val(),
                code: $('#edit-course-code').val(),
                institution_id: $('#edit-course-institution').val(),
                semester: $('#edit-course-semester').val()
            },
            success: function(response) {
                if (response.success) {
                    modals.editCourse.hide();
                    loadCourseData();
                    showSuccess('הקורס עודכן בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function exportStudents() {
        window.location.href = edutrackAdmin.ajaxUrl +
            '?action=edutrack_export_students&course_id=' + courseId +
            '&nonce=' + edutrackAdmin.nonce;
    }

    function editStudent(student) {
        $('#edit-student-id').val(student.id);
        $('#edit-first-name').val(student.first_name);
        $('#edit-last-name').val(student.last_name);
        $('#edit-phone').val(student.phone);
        $('#edit-email').val(student.email || '');
        modals.editStudent.show();
    }

    function updateStudent() {
        const studentId = $('#edit-student-id').val();
        const formData = {
            action: 'edutrack_update_student',
            nonce: edutrackAdmin.nonce,
            student_id: studentId,
            first_name: $('#edit-first-name').val(),
            last_name: $('#edit-last-name').val(),
            phone: $('#edit-phone').val(),
            email: $('#edit-email').val()
        };

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    modals.editStudent.hide();
                    loadStudents();
                    showSuccess('התלמיד עודכן בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('שגיאה בעדכון התלמיד');
            }
        });
    }

    function downloadCSVTemplate() {
        const template = 'שם פרטי,שם משפחה,טלפון,אימייל\n' +
                        'יוסי,כהן,0501234567,yossi@example.com\n' +
                        'מיכל,לוי,0529876543,michal@example.com\n' +
                        'דוד,אברהם,0547654321,david@example.com';

        const blob = new Blob(['\ufeff' + template], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'students_template.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showSuccess('תבנית CSV הורדה בהצלחה!');
    }

    function previewCSV() {
        const csvData = $('#csv-content').val();
        if (!csvData.trim()) {
            alert('נא להזין או להעלות תוכן CSV');
            return;
        }

        const lines = csvData.trim().split('\n');
        const warnings = [];
        let html = '<table class="table table-sm table-bordered"><thead><tr>';
        html += '<th>#</th><th>שם פרטי</th><th>שם משפחה</th><th>טלפון</th><th>אימייל</th><th>סטטוס</th>';
        html += '</tr></thead><tbody>';

        let validCount = 0;
        lines.forEach((line, index) => {
            if (index === 0) return; // Skip header

            const cols = line.split(',').map(col => col.trim());
            if (cols.length < 3) return; // Skip invalid lines

            const firstName = cols[0];
            const lastName = cols[1];
            const phone = cols[2];
            const email = cols[3] || '';

            let status = '<span class="badge bg-success">תקין</span>';
            let rowClass = '';

            // Validate phone
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length !== 10 && cleanPhone.length !== 12) {
                status = '<span class="badge bg-danger">טלפון לא תקין</span>';
                rowClass = 'table-danger';
                warnings.push(`שורה ${index + 1}: מספר טלפון לא תקין (${phone})`);
            }

            // Validate required fields
            if (!firstName || !lastName) {
                status = '<span class="badge bg-danger">שדות חסרים</span>';
                rowClass = 'table-danger';
                warnings.push(`שורה ${index + 1}: חסר שם פרטי או משפחה`);
            } else {
                validCount++;
            }

            html += `<tr class="${rowClass}">`;
            html += `<td>${index}</td>`;
            html += `<td>${escapeHtml(firstName)}</td>`;
            html += `<td>${escapeHtml(lastName)}</td>`;
            html += `<td>${escapeHtml(phone)}</td>`;
            html += `<td>${escapeHtml(email)}</td>`;
            html += `<td>${status}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += `<div class="alert alert-info mt-2">`;
        html += `<strong>סיכום:</strong> ${validCount} תלמידים תקינים מתוך ${lines.length - 1} שורות`;
        html += '</div>';

        $('#preview-content').html(html);

        if (warnings.length > 0) {
            let warningHtml = '';
            warnings.forEach(w => {
                warningHtml += `<li>${w}</li>`;
            });
            $('#warning-list').html(warningHtml);
            $('#preview-warnings').show();
        } else {
            $('#preview-warnings').hide();
        }

        $('#import-preview').show();
    }

    function loadLessons() {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_get_lessons',
                nonce: edutrackAdmin.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    displayLessons(response.data);
                } else {
                    $('#lessons-list').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
            }
        });
    }

    function displayLessons(lessons) {
        if (lessons.length === 0) {
            $('#lessons-list').html('<div class="alert alert-info">אין מפגשים מתוכננים. הוסף מפגש ראשון!</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr>';
        html += '<th>#</th><th>כותרת</th><th>תאריך</th><th>שעה</th><th>משך</th><th>סטטוס</th><th>פעולות</th>';
        html += '</tr></thead><tbody>';

        lessons.forEach((lesson, index) => {
            const plannedDate = lesson.planned_date ? formatDate(lesson.planned_date) : '-';
            const plannedTime = lesson.planned_time || '-';
            const duration = lesson.duration ? lesson.duration + ' דקות' : '-';

            let status = '<span class="badge bg-secondary">מתוכנן</span>';
            if (lesson.status === 'completed') {
                status = '<span class="badge bg-success">הושלם</span>';
            } else if (lesson.status === 'cancelled') {
                status = '<span class="badge bg-danger">בוטל</span>';
            }

            html += '<tr>';
            html += '<td><strong>' + lesson.lesson_number + '</strong></td>';
            html += '<td>' + escapeHtml(lesson.title) + '</td>';
            html += '<td>' + plannedDate + '</td>';
            html += '<td>' + plannedTime + '</td>';
            html += '<td>' + duration + '</td>';
            html += '<td>' + status + '</td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-info btn-edit-lesson me-1" data-lesson=\'' + JSON.stringify(lesson) + '\'>';
            html += '<i class="bi bi-pencil"></i></button>';
            html += '<button class="btn btn-sm btn-danger btn-delete-lesson" data-id="' + lesson.id + '">';
            html += '<i class="bi bi-trash"></i></button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#lessons-list').html(html);

        // Edit handlers
        $('.btn-edit-lesson').click(function() {
            const lesson = $(this).data('lesson');
            editLesson(lesson);
        });

        // Delete handlers
        $('.btn-delete-lesson').click(function() {
            if (confirm('האם אתה בטוח שברצונך למחוק מפגש זה?')) {
                deleteLesson($(this).data('id'));
            }
        });
    }

    function saveLesson() {
        const formData = {
            action: 'edutrack_add_lesson',
            nonce: edutrackAdmin.nonce,
            course_id: courseId,
            lesson_number: $('input[name="lesson_number"]').val(),
            title: $('input[name="title"]').val(),
            description: $('textarea[name="description"]').val(),
            planned_date: $('input[name="planned_date"]').val(),
            planned_time: $('input[name="planned_time"]').val(),
            duration: $('input[name="duration"]').val()
        };

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    modals.addLesson.hide();
                    $('#add-lesson-form')[0].reset();
                    loadLessons();
                    showSuccess('המפגש נוסף בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function editLesson(lesson) {
        $('#edit-lesson-id').val(lesson.id);
        $('#edit-lesson-number').val(lesson.lesson_number);
        $('#edit-lesson-title').val(lesson.title);
        $('#edit-lesson-description').val(lesson.description || '');
        $('#edit-lesson-date').val(lesson.planned_date);
        $('#edit-lesson-time').val(lesson.planned_time);
        $('#edit-lesson-duration').val(lesson.duration);
        modals.editLesson.show();
    }

    function updateLesson() {
        const lessonId = $('#edit-lesson-id').val();
        const formData = {
            action: 'edutrack_update_lesson',
            nonce: edutrackAdmin.nonce,
            lesson_id: lessonId,
            lesson_number: $('#edit-lesson-number').val(),
            title: $('#edit-lesson-title').val(),
            description: $('#edit-lesson-description').val(),
            planned_date: $('#edit-lesson-date').val(),
            planned_time: $('#edit-lesson-time').val(),
            duration: $('#edit-lesson-duration').val()
        };

        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    modals.editLesson.hide();
                    loadLessons();
                    showSuccess('המפגש עודכן בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('שגיאה בעדכון המפגש');
            }
        });
    }

    function deleteLesson(lessonId) {
        $.ajax({
            url: edutrackAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'edutrack_delete_lesson',
                nonce: edutrackAdmin.nonce,
                lesson_id: lessonId
            },
            success: function(response) {
                if (response.success) {
                    loadLessons();
                    showSuccess('המפגש נמחק בהצלחה!');
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    function viewActiveSession() {
        if (activeSession) {
            window.location.href = edutrackAdmin.siteUrl +
                '/wp-admin/admin.php?page=edutrack-session&session_id=' + activeSession.id;
        }
    }

    function viewSession(sessionId) {
        window.location.href = edutrackAdmin.siteUrl +
            '/wp-admin/admin.php?page=edutrack-session&session_id=' + sessionId;
    }

    // === UTILITY FUNCTIONS ===

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
        const notification = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;">' +
            escapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        $('body').append(notification);
        setTimeout(() => notification.fadeOut(() => notification.remove()), 3000);
    }

    function showError(message) {
        alert(message);
    }
});
</script>
