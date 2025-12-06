<?php
/**
 * AJAX handlers for EduTrack Lite.
 *
 * This class handles all AJAX requests from the frontend and admin.
 */
class Edutrack_Ajax
{
    /**
     * Get all courses for current user.
     */
    public function get_courses()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_courses')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $table_institutions = Edutrack_Database::get_table_name('institutions');
        $user_id = get_current_user_id();

        $courses = $wpdb->get_results($wpdb->prepare("
            SELECT c.*, i.name as institution_name,
            (SELECT COUNT(*) FROM " . Edutrack_Database::get_table_name('students') . " WHERE course_id = c.id) as student_count,
            (SELECT COUNT(*) FROM " . Edutrack_Database::get_table_name('attendance_sessions') . " WHERE course_id = c.id AND status = 'closed') as session_count
            FROM {$table_courses} c
            LEFT JOIN {$table_institutions} i ON c.institution_id = i.id
            WHERE c.lecturer_id = %d AND c.status = 'active'
            ORDER BY c.created_at DESC
        ", $user_id), ARRAY_A);

        wp_send_json_success($courses);
    }

    /**
     * Get single course details.
     */
    public function get_course()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_courses')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_send_json_error(array('message' => 'חסר מזהה קורס'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $table_institutions = Edutrack_Database::get_table_name('institutions');
        $user_id = get_current_user_id();

        $course = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, i.name as institution_name,
            (SELECT COUNT(*) FROM " . Edutrack_Database::get_table_name('students') . " WHERE course_id = c.id) as student_count,
            (SELECT COUNT(*) FROM " . Edutrack_Database::get_table_name('attendance_sessions') . " WHERE course_id = c.id AND status = 'closed') as session_count
            FROM {$table_courses} c
            LEFT JOIN {$table_institutions} i ON c.institution_id = i.id
            WHERE c.id = %d AND c.lecturer_id = %d
        ", $course_id, $user_id), ARRAY_A);

        if (!$course) {
            wp_send_json_error(array('message' => 'קורס לא נמצא'), 404);
        }

        wp_send_json_success($course);
    }

    /**
     * Create a new course.
     */
    public function create_course()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_courses')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $institution_id = isset($_POST['institution_id']) ? intval($_POST['institution_id']) : 0;
        $semester = isset($_POST['semester']) ? sanitize_text_field($_POST['semester']) : '';

        if (empty($name) || empty($semester) || !$institution_id) {
            wp_send_json_error(array('message' => 'נא למלא את כל השדות החובה'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        $result = $wpdb->insert(
            $table_courses,
            array(
                'name' => $name,
                'code' => $code,
                'institution_id' => $institution_id,
                'semester' => $semester,
                'lecturer_id' => $user_id,
                'status' => 'active',
            ),
            array('%s', '%s', '%d', '%s', '%d', '%s')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה ביצירת הקורס'));
        }

        wp_send_json_success(array(
            'course_id' => $wpdb->insert_id,
            'message' => 'הקורס נוצר בהצלחה'
        ));
    }

    /**
     * Update an existing course.
     */
    public function update_course()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_courses')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $institution_id = isset($_POST['institution_id']) ? intval($_POST['institution_id']) : 0;
        $semester = isset($_POST['semester']) ? sanitize_text_field($_POST['semester']) : '';

        if (!$course_id || empty($name) || empty($semester) || !$institution_id) {
            wp_send_json_error(array('message' => 'נא למלא את כל השדות החובה'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות לערוך קורס זה'), 403);
        }

        $result = $wpdb->update(
            $table_courses,
            array(
                'name' => $name,
                'code' => $code,
                'institution_id' => $institution_id,
                'semester' => $semester,
            ),
            array('id' => $course_id),
            array('%s', '%s', '%d', '%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה בעדכון הקורס'));
        }

        wp_send_json_success(array('message' => 'הקורס עודכן בהצלחה'));
    }

    /**
     * Delete a course.
     */
    public function delete_course()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_courses')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_send_json_error(array('message' => 'חסר מזהה קורס'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות למחוק קורס זה'), 403);
        }

        // Soft delete - archive instead of delete
        $result = $wpdb->update(
            $table_courses,
            array('status' => 'archived'),
            array('id' => $course_id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה במחיקת הקורס'));
        }

        wp_send_json_success(array('message' => 'הקורס נמחק בהצלחה'));
    }

    /**
     * Get students for a course.
     */
    public function get_students()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_students')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_send_json_error(array('message' => 'חסר מזהה קורס'));
        }

        global $wpdb;
        $table_students = Edutrack_Database::get_table_name('students');
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $students = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table_students}
            WHERE course_id = %d
            ORDER BY last_name, first_name
        ", $course_id), ARRAY_A);

        wp_send_json_success($students);
    }

    /**
     * Add a student to a course.
     */
    public function add_student()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_students')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!$course_id || empty($first_name) || empty($last_name) || empty($phone)) {
            wp_send_json_error(array('message' => 'נא למלא את כל השדות החובה'));
        }

        // Validate phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) !== 10) {
            wp_send_json_error(array('message' => 'מספר טלפון לא תקין (צריך 10 ספרות)'));
        }

        global $wpdb;
        $table_students = Edutrack_Database::get_table_name('students');
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $result = $wpdb->insert(
            $table_students,
            array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'course_id' => $course_id,
            ),
            array('%s', '%s', '%s', '%s', '%d')
        );

        if ($result === false) {
            // Check if it's a duplicate error
            if (strpos($wpdb->last_error, 'unique_student_course') !== false) {
                wp_send_json_error(array('message' => 'תלמיד עם מספר טלפון זה כבר קיים בקורס'));
            }
            wp_send_json_error(array('message' => 'שגיאה בהוספת התלמיד'));
        }

        wp_send_json_success(array(
            'student_id' => $wpdb->insert_id,
            'message' => 'התלמיד נוסף בהצלחה'
        ));
    }

    /**
     * Import students from CSV.
     */
    public function import_students()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_students')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $csv_data = isset($_POST['csv_data']) ? $_POST['csv_data'] : '';

        if (!$course_id || empty($csv_data)) {
            wp_send_json_error(array('message' => 'נתונים חסרים'));
        }

        global $wpdb;
        $table_students = Edutrack_Database::get_table_name('students');
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        // Parse CSV
        $lines = explode("\n", $csv_data);
        $success_count = 0;
        $error_count = 0;
        $errors = array();

        foreach ($lines as $line_number => $line) {
            $line = trim($line);
            if (empty($line) || $line_number === 0) {
                continue; // Skip header
            }

            $data = str_getcsv($line);
            if (count($data) < 3) {
                $error_count++;
                $errors[] = "שורה {$line_number}: נתונים חסרים";
                continue;
            }

            $first_name = sanitize_text_field($data[0]);
            $last_name = sanitize_text_field($data[1]);
            $phone = preg_replace('/[^0-9]/', '', $data[2]);
            $email = isset($data[3]) ? sanitize_email($data[3]) : '';

            if (strlen($phone) !== 10) {
                $error_count++;
                $errors[] = "שורה {$line_number}: מספר טלפון לא תקין";
                continue;
            }

            $result = $wpdb->insert(
                $table_students,
                array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'email' => $email,
                    'course_id' => $course_id,
                ),
                array('%s', '%s', '%s', '%s', '%d')
            );

            if ($result !== false) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "שורה {$line_number}: {$first_name} {$last_name} - כבר קיים או שגיאה";
            }
        }

        wp_send_json_success(array(
            'message' => "ייבוא הושלם: {$success_count} נוספו, {$error_count} נכשלו",
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors,
        ));
    }

    /**
     * Delete a student.
     */
    public function delete_student()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_students')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
        if (!$student_id) {
            wp_send_json_error(array('message' => 'חסר מזהה תלמיד'));
        }

        global $wpdb;
        $table_students = Edutrack_Database::get_table_name('students');
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Verify ownership
        $course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT course_id FROM {$table_students} WHERE id = %d",
            $student_id
        ));

        if (!$course_id) {
            wp_send_json_error(array('message' => 'תלמיד לא נמצא'), 404);
        }

        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $result = $wpdb->delete(
            $table_students,
            array('id' => $student_id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה במחיקת התלמיד'));
        }

        wp_send_json_success(array('message' => 'התלמיד נמחק בהצלחה'));
    }

    /**
     * Start an attendance session.
     */
    public function start_session()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_attendance')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null;

        if (!$course_id) {
            wp_send_json_error(array('message' => 'חסר מזהה קורס'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        // Check if there's already an active session for this course
        $active_session = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$table_sessions}
            WHERE course_id = %d AND status = 'active'
        ", $course_id), ARRAY_A);

        if ($active_session) {
            wp_send_json_success(array(
                'session' => $active_session,
                'message' => 'כבר יש שיעור פעיל לקורס זה'
            ));
            return;
        }

        // Generate unique token
        $token = bin2hex(random_bytes(16));

        // Get session timeout from settings
        $settings = get_option('edutrack_settings', array('session_timeout' => 30));
        $timeout = isset($settings['session_timeout']) ? intval($settings['session_timeout']) : 30;

        $expires_at = date('Y-m-d H:i:s', strtotime("+{$timeout} minutes"));

        $result = $wpdb->insert(
            $table_sessions,
            array(
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'token' => $token,
                'expires_at' => $expires_at,
                'status' => 'active',
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה ביצירת השיעור'));
        }

        $session_id = $wpdb->insert_id;
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_sessions} WHERE id = %d",
            $session_id
        ), ARRAY_A);

        wp_send_json_success(array(
            'session' => $session,
            'message' => 'השיעור נפתח בהצלחה'
        ));
    }

    /**
     * Close an attendance session.
     */
    public function close_session()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_attendance')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
        if (!$session_id) {
            wp_send_json_error(array('message' => 'חסר מזהה שיעור'));
        }

        global $wpdb;
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $table_courses = Edutrack_Database::get_table_name('courses');
        $user_id = get_current_user_id();

        // Get session
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_sessions} WHERE id = %d",
            $session_id
        ), ARRAY_A);

        if (!$session) {
            wp_send_json_error(array('message' => 'שיעור לא נמצא'), 404);
        }

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $session['course_id']
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $result = $wpdb->update(
            $table_sessions,
            array('status' => 'closed'),
            array('id' => $session_id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה בסגירת השיעור'));
        }

        wp_send_json_success(array('message' => 'השיעור נסגר בהצלחה'));
    }

    /**
     * Get session status and attendance.
     */
    public function get_session_status()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
        if (!$session_id) {
            wp_send_json_error(array('message' => 'חסר מזהה שיעור'));
        }

        global $wpdb;
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $table_records = Edutrack_Database::get_table_name('attendance_records');
        $table_students = Edutrack_Database::get_table_name('students');

        // Get session
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_sessions} WHERE id = %d",
            $session_id
        ), ARRAY_A);

        if (!$session) {
            wp_send_json_error(array('message' => 'שיעור לא נמצא'), 404);
        }

        // Get attendance records
        $records = $wpdb->get_results($wpdb->prepare("
            SELECT ar.*, s.first_name, s.last_name, s.phone
            FROM {$table_records} ar
            JOIN {$table_students} s ON ar.student_id = s.id
            WHERE ar.session_id = %d
            ORDER BY ar.timestamp DESC
        ", $session_id), ARRAY_A);

        // Check if expired
        $is_expired = strtotime($session['expires_at']) < time();

        wp_send_json_success(array(
            'session' => $session,
            'records' => $records,
            'is_expired' => $is_expired,
            'count' => count($records),
        ));
    }

    /**
     * Get attendance records for a course.
     */
    public function get_attendance()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        if (!current_user_can('manage_edutrack_attendance')) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_send_json_error(array('message' => 'חסר מזהה קורס'));
        }

        global $wpdb;
        $table_courses = Edutrack_Database::get_table_name('courses');
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $table_records = Edutrack_Database::get_table_name('attendance_records');
        $table_students = Edutrack_Database::get_table_name('students');
        $user_id = get_current_user_id();

        // Verify ownership
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT lecturer_id FROM {$table_courses} WHERE id = %d",
            $course_id
        ));

        if ($owner != $user_id) {
            wp_send_json_error(array('message' => 'אין לך הרשאות'), 403);
        }

        // Get all sessions
        $sessions = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table_sessions}
            WHERE course_id = %d
            ORDER BY started_at DESC
        ", $course_id), ARRAY_A);

        // Get all students
        $students = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table_students}
            WHERE course_id = %d
            ORDER BY last_name, first_name
        ", $course_id), ARRAY_A);

        // Get attendance matrix
        $attendance = array();
        foreach ($students as $student) {
            $student_attendance = array(
                'student' => $student,
                'sessions' => array(),
            );

            foreach ($sessions as $session) {
                $attended = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM {$table_records}
                    WHERE session_id = %d AND student_id = %d
                ", $session['id'], $student['id']));

                $student_attendance['sessions'][$session['id']] = $attended > 0;
            }

            $attendance[] = $student_attendance;
        }

        wp_send_json_success(array(
            'sessions' => $sessions,
            'students' => $students,
            'attendance' => $attendance,
        ));
    }

    /**
     * Mark attendance (public - for students).
     */
    public function mark_attendance()
    {
        // Public endpoint - verify with different nonce
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'edutrack_public_nonce')) {
            wp_send_json_error(array('message' => 'אימות נכשל'), 403);
        }

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        if (empty($token) || empty($phone)) {
            wp_send_json_error(array('message' => 'נתונים חסרים'));
        }

        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) !== 10) {
            wp_send_json_error(array('message' => 'מספר טלפון לא תקין'));
        }

        global $wpdb;
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $table_students = Edutrack_Database::get_table_name('students');
        $table_records = Edutrack_Database::get_table_name('attendance_records');

        // Get session
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_sessions} WHERE token = %s AND status = 'active'",
            $token
        ), ARRAY_A);

        if (!$session) {
            wp_send_json_error(array('message' => 'שיעור לא נמצא או לא פעיל'));
        }

        // Check if expired
        if (strtotime($session['expires_at']) < time()) {
            wp_send_json_error(array('message' => 'השיעור פג תוקף'));
        }

        // Find student
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_students} WHERE phone = %s AND course_id = %d",
            $phone,
            $session['course_id']
        ), ARRAY_A);

        if (!$student) {
            wp_send_json_error(array('message' => 'מספר טלפון לא נמצא ברשימת התלמידים'));
        }

        // Check if already marked
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_records} WHERE session_id = %d AND student_id = %d",
            $session['id'],
            $student['id']
        ));

        if ($existing > 0) {
            wp_send_json_success(array(
                'message' => 'כבר נרשמת לשיעור זה',
                'student_name' => $student['first_name'] . ' ' . $student['last_name'],
            ));
            return;
        }

        // Mark attendance
        $result = $wpdb->insert(
            $table_records,
            array(
                'session_id' => $session['id'],
                'student_id' => $student['id'],
                'phone_entered' => $phone,
            ),
            array('%d', '%d', '%s')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'שגיאה ברישום הנוכחות'));
        }

        wp_send_json_success(array(
            'message' => 'נוכחות נרשמה בהצלחה',
            'student_name' => $student['first_name'] . ' ' . $student['last_name'],
        ));
    }

    /**
     * Verify session (public).
     */
    public function verify_session()
    {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        if (empty($token)) {
            wp_send_json_error(array('message' => 'חסר טוקן'));
        }

        global $wpdb;
        $table_sessions = Edutrack_Database::get_table_name('attendance_sessions');
        $table_courses = Edutrack_Database::get_table_name('courses');

        $session = $wpdb->get_row($wpdb->prepare("
            SELECT s.*, c.name as course_name
            FROM {$table_sessions} s
            JOIN {$table_courses} c ON s.course_id = c.id
            WHERE s.token = %s AND s.status = 'active'
        ", $token), ARRAY_A);

        if (!$session) {
            wp_send_json_error(array('message' => 'שיעור לא נמצא או לא פעיל'), 404);
        }

        $is_expired = strtotime($session['expires_at']) < time();

        wp_send_json_success(array(
            'session' => $session,
            'is_expired' => $is_expired,
        ));
    }

    /**
     * Get institutions list.
     */
    public function get_institutions()
    {
        check_ajax_referer('edutrack_nonce', 'nonce');

        global $wpdb;
        $table_institutions = Edutrack_Database::get_table_name('institutions');

        $institutions = $wpdb->get_results(
            "SELECT * FROM {$table_institutions} ORDER BY name",
            ARRAY_A
        );

        wp_send_json_success($institutions);
    }
}
