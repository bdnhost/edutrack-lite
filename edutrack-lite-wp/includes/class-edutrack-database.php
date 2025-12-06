<?php
/**
 * Database operations for EduTrack Lite.
 *
 * This class handles all database table creation, migrations, and schema updates.
 * Uses WordPress wpdb for database operations.
 */
class Edutrack_Database
{
    /**
     * Create all required database tables.
     */
    public static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix . 'edutrack_';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // 1. Institutions table
        $sql_institutions = "CREATE TABLE IF NOT EXISTS {$table_prefix}institutions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_institutions);

        // Insert default institutions
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}institutions");
        if ($existing == 0) {
            $wpdb->insert(
                $table_prefix . 'institutions',
                array('name' => 'מכללת עתיד')
            );
            $wpdb->insert(
                $table_prefix . 'institutions',
                array('name' => 'מכללת מערב הגליל')
            );
            $wpdb->insert(
                $table_prefix . 'institutions',
                array('name' => 'מכללת ORT')
            );
            $wpdb->insert(
                $table_prefix . 'institutions',
                array('name' => 'אחר')
            );
        }

        // 2. Courses table
        // Note: Uses wp_user_id instead of separate users table
        $sql_courses = "CREATE TABLE IF NOT EXISTS {$table_prefix}courses (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            code varchar(50) DEFAULT NULL,
            institution_id bigint(20) UNSIGNED NOT NULL,
            semester varchar(50) NOT NULL,
            lecturer_id bigint(20) UNSIGNED NOT NULL COMMENT 'WordPress user ID',
            status enum('active','archived') DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY lecturer_id (lecturer_id),
            KEY institution_id (institution_id),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_courses);

        // 3. Students table
        $sql_students = "CREATE TABLE IF NOT EXISTS {$table_prefix}students (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            email varchar(200) DEFAULT NULL,
            course_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY phone (phone),
            UNIQUE KEY unique_student_course (phone, course_id)
        ) $charset_collate;";

        dbDelta($sql_students);

        // 4. Lessons table
        $sql_lessons = "CREATE TABLE IF NOT EXISTS {$table_prefix}lessons (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id bigint(20) UNSIGNED NOT NULL,
            lesson_number int(11) NOT NULL COMMENT 'מספר המפגש',
            title varchar(200) NOT NULL,
            description text,
            planned_date date DEFAULT NULL,
            planned_time time DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            UNIQUE KEY unique_lesson_number (course_id, lesson_number)
        ) $charset_collate;";

        dbDelta($sql_lessons);

        // 5. Attendance Sessions table
        $sql_sessions = "CREATE TABLE IF NOT EXISTS {$table_prefix}attendance_sessions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id bigint(20) UNSIGNED NOT NULL,
            lesson_id bigint(20) UNSIGNED DEFAULT NULL,
            token varchar(100) NOT NULL,
            started_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            status enum('active','closed') DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_sessions);

        // 6. Attendance Records table
        $sql_records = "CREATE TABLE IF NOT EXISTS {$table_prefix}attendance_records (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id bigint(20) UNSIGNED NOT NULL,
            student_id bigint(20) UNSIGNED NOT NULL,
            phone_entered varchar(20) NOT NULL,
            timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY student_id (student_id),
            UNIQUE KEY unique_attendance (session_id, student_id)
        ) $charset_collate;";

        dbDelta($sql_records);
    }

    /**
     * Drop all plugin tables.
     * Used during uninstall if user chooses to delete data.
     */
    public static function drop_tables()
    {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'edutrack_';

        $tables = array(
            'attendance_records',
            'attendance_sessions',
            'lessons',
            'students',
            'courses',
            'institutions'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_prefix}{$table}");
        }
    }

    /**
     * Get table name with prefix.
     */
    public static function get_table_name($table)
    {
        global $wpdb;
        return $wpdb->prefix . 'edutrack_' . $table;
    }

    /**
     * Check if a table exists.
     */
    public static function table_exists($table)
    {
        global $wpdb;
        $table_name = self::get_table_name($table);
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    }

    /**
     * Get database version.
     */
    public static function get_db_version()
    {
        return get_option('edutrack_db_version', '1.0.0');
    }

    /**
     * Update database version.
     */
    public static function update_db_version($version)
    {
        update_option('edutrack_db_version', $version);
    }
}
