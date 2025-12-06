-- ========================================
-- EduTrack Lite - Full Migration Script
-- ========================================
-- קובץ זה מכיל את כל השינויים הנדרשים למסד הנתונים
-- הרץ את הקובץ הזה דרך phpMyAdmin או MySQL CLI
-- ========================================

-- בדיקה ראשונית
SELECT 'Starting EduTrack Lite Full Migration...' as status;

-- ========================================
-- PART 1: שינוי user_id ל-lecturer_id
-- ========================================

-- בדוק אם העמודה user_id קיימת
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'courses' 
    AND COLUMN_NAME = 'user_id'
);

-- אם user_id קיים, בצע את השינוי
SET @sql = IF(@column_exists > 0,
    'ALTER TABLE courses DROP FOREIGN KEY courses_ibfk_1',
    'SELECT "user_id foreign key already removed or does not exist" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- שנה את שם העמודה
SET @sql = IF(@column_exists > 0,
    'ALTER TABLE courses CHANGE user_id lecturer_id int(11) NOT NULL COMMENT "מזהה המרצה (מטבלת users)"',
    'SELECT "Column already renamed or does not exist" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- הוסף Foreign Key חדש
SET @sql = IF(@column_exists > 0,
    'ALTER TABLE courses ADD CONSTRAINT courses_lecturer_fk FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE',
    'SELECT "Foreign key already exists" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- עדכן אינדקס
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'courses' 
    AND INDEX_NAME = 'user_id'
);

SET @sql = IF(@index_exists > 0,
    'ALTER TABLE courses DROP INDEX user_id',
    'SELECT "Old index already removed" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- הוסף אינדקס חדש
SET @new_index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'courses' 
    AND INDEX_NAME = 'lecturer_id'
);

SET @sql = IF(@new_index_exists = 0,
    'ALTER TABLE courses ADD INDEX lecturer_id (lecturer_id)',
    'SELECT "New index already exists" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Part 1 completed: user_id → lecturer_id' as status;

-- ========================================
-- PART 2: יצירת טבלת lessons (אם לא קיימת)
-- ========================================

CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lesson_number` int(11) NOT NULL COMMENT 'מספר המפגש (1, 2, 3...)',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `planned_date` date DEFAULT NULL,
  `planned_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  UNIQUE KEY `unique_lesson_number` (`course_id`, `lesson_number`),
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Part 2 completed: lessons table created' as status;

-- ========================================
-- PART 3: הוספת lesson_id ל-attendance_sessions
-- ========================================

-- בדוק אם העמודה lesson_id כבר קיימת
SET @lesson_id_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'attendance_sessions' 
    AND COLUMN_NAME = 'lesson_id'
);

-- הוסף את העמודה אם היא לא קיימת
SET @sql = IF(@lesson_id_exists = 0,
    'ALTER TABLE attendance_sessions ADD COLUMN lesson_id int(11) DEFAULT NULL COMMENT "קישור למפגש ספציפי (אופציונלי)" AFTER course_id',
    'SELECT "lesson_id column already exists" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- הוסף Foreign Key
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'attendance_sessions' 
    AND COLUMN_NAME = 'lesson_id'
    AND REFERENCED_TABLE_NAME = 'lessons'
);

SET @sql = IF(@fk_exists = 0 AND @lesson_id_exists = 0,
    'ALTER TABLE attendance_sessions ADD CONSTRAINT attendance_sessions_lesson_fk FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- הוסף אינדקס
SET @lesson_index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'attendance_sessions' 
    AND INDEX_NAME = 'lesson_id'
);

SET @sql = IF(@lesson_index_exists = 0 AND @lesson_id_exists = 0,
    'ALTER TABLE attendance_sessions ADD INDEX lesson_id (lesson_id)',
    'SELECT "Index already exists" as status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Part 3 completed: lesson_id added to attendance_sessions' as status;

-- ========================================
-- PART 4: יצירת/עדכון Views
-- ========================================

-- View: סטטיסטיקת נוכחות לפי תלמיד
DROP VIEW IF EXISTS `student_attendance_stats`;
CREATE VIEW `student_attendance_stats` AS
SELECT 
  s.id as student_id,
  s.first_name,
  s.last_name,
  s.phone,
  s.course_id,
  COUNT(DISTINCT ar.id) as total_attended,
  (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = s.course_id AND status = 'closed') as total_sessions,
  CASE 
    WHEN (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = s.course_id AND status = 'closed') > 0
    THEN ROUND((COUNT(DISTINCT ar.id) / 
          (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = s.course_id AND status = 'closed') * 100), 2)
    ELSE 0
  END as attendance_percentage
FROM students s
LEFT JOIN attendance_records ar ON ar.student_id = s.id
GROUP BY s.id, s.first_name, s.last_name, s.phone, s.course_id;

SELECT 'View created: student_attendance_stats' as status;

-- View: סטטיסטיקת נוכחות לפי קורס
DROP VIEW IF EXISTS `course_attendance_stats`;
CREATE VIEW `course_attendance_stats` AS
SELECT 
  c.id as course_id,
  c.name as course_name,
  COUNT(DISTINCT s.id) as total_students,
  COUNT(DISTINCT ats.id) as total_sessions,
  COUNT(ar.id) as total_attendance_records,
  CASE 
    WHEN COUNT(DISTINCT s.id) > 0 AND COUNT(DISTINCT ats.id) > 0
    THEN ROUND((COUNT(ar.id) / (COUNT(DISTINCT s.id) * COUNT(DISTINCT ats.id)) * 100), 2)
    ELSE 0
  END as average_attendance_percentage
FROM courses c
LEFT JOIN students s ON s.course_id = c.id
LEFT JOIN attendance_sessions ats ON ats.course_id = c.id AND ats.status = 'closed'
LEFT JOIN attendance_records ar ON ar.session_id = ats.id
GROUP BY c.id, c.name;

SELECT 'View created: course_attendance_stats' as status;

-- View: סטטיסטיקת מפגשים
DROP VIEW IF EXISTS `lesson_stats`;
CREATE VIEW `lesson_stats` AS
SELECT 
  l.id as lesson_id,
  l.course_id,
  l.lesson_number,
  l.title,
  l.planned_date,
  COUNT(DISTINCT ats.id) as sessions_count,
  COUNT(DISTINCT ar.id) as total_attendances,
  (SELECT COUNT(*) FROM students WHERE course_id = l.course_id) as total_students,
  CASE 
    WHEN (SELECT COUNT(*) FROM students WHERE course_id = l.course_id) > 0 
         AND COUNT(DISTINCT ats.id) > 0
    THEN ROUND((COUNT(DISTINCT ar.id) / 
          ((SELECT COUNT(*) FROM students WHERE course_id = l.course_id) * COUNT(DISTINCT ats.id)) * 100), 2)
    ELSE 0
  END as attendance_percentage
FROM lessons l
LEFT JOIN attendance_sessions ats ON ats.lesson_id = l.id AND ats.status = 'closed'
LEFT JOIN attendance_records ar ON ar.session_id = ats.id
GROUP BY l.id, l.course_id, l.lesson_number, l.title, l.planned_date;

SELECT 'View created: lesson_stats' as status;

-- ========================================
-- PART 5: בדיקות סופיות
-- ========================================

-- בדוק שהטבלאות קיימות
SELECT 
    CASE 
        WHEN COUNT(*) = 8 THEN '✅ All tables exist'
        ELSE '❌ Some tables are missing'
    END as table_check
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN (
    'users', 'institutions', 'courses', 'students', 
    'lessons', 'attendance_sessions', 'attendance_records'
);

-- בדוק שהעמודה lecturer_id קיימת
SELECT 
    CASE 
        WHEN COUNT(*) = 1 THEN '✅ lecturer_id column exists'
        ELSE '❌ lecturer_id column missing'
    END as lecturer_id_check
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'courses' 
AND COLUMN_NAME = 'lecturer_id';

-- בדוק שהעמודה lesson_id קיימת
SELECT 
    CASE 
        WHEN COUNT(*) = 1 THEN '✅ lesson_id column exists'
        ELSE '❌ lesson_id column missing'
    END as lesson_id_check
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'attendance_sessions' 
AND COLUMN_NAME = 'lesson_id';

-- בדוק שה-Views קיימים
SELECT 
    CASE 
        WHEN COUNT(*) = 3 THEN '✅ All views exist'
        ELSE '❌ Some views are missing'
    END as views_check
FROM INFORMATION_SCHEMA.VIEWS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN (
    'student_attendance_stats', 
    'course_attendance_stats',
    'lesson_stats'
);

-- ========================================
-- סיכום
-- ========================================

SELECT '
========================================
✅ Migration Completed Successfully!
========================================

Changes applied:
1. ✅ user_id → lecturer_id in courses table
2. ✅ lessons table created
3. ✅ lesson_id added to attendance_sessions
4. ✅ All views created/updated

Next steps:
1. Test the application: https://bdnhost.net/edutrack-lite/
2. Try to login and create a course
3. If everything works, delete debug files:
   - api/debug_session.php
   - api/test_db.php

========================================
' as MIGRATION_SUMMARY;
