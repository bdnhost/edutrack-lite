-- EduTrack Lite - MySQL Database Schema
-- התקנה: ייבא את הקובץ הזה דרך phpMyAdmin או MySQL CLI

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ========================================
-- 1. מוסדות לימוד
-- ========================================
CREATE TABLE IF NOT EXISTS `institutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- נתונים ראשוניים
INSERT INTO `institutions` (`name`) VALUES
('מכללת עתיד'),
('מכללת מערב הגליל'),
('מכללת ORT'),
('אחר');

-- ========================================
-- 2. משתמשים (מרצים)
-- ========================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. קורסים
-- ========================================
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_id` int(11) NOT NULL,
  `semester` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lecturer_id` int(11) NOT NULL COMMENT 'מזהה המרצה (מטבלת users)',
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lecturer_id` (`lecturer_id`),
  KEY `institution_id` (`institution_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. תלמידים
-- ========================================
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `phone` (`phone`),
  UNIQUE KEY `unique_student_course` (`phone`, `course_id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. מפגשים (Lessons)
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

-- ========================================
-- 6. שיעורים (סשנים) - מפגשי נוכחות
-- ========================================
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL COMMENT 'קישור למפגש ספציפי (אופציונלי)',
  `token` varchar(100) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `course_id` (`course_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. רישום נוכחות
-- ========================================
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `phone_entered` varchar(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `student_id` (`student_id`),
  UNIQUE KEY `unique_attendance` (`session_id`, `student_id`),
  FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VIEWS לדוחות
-- ========================================

-- סטטיסטיקת נוכחות לפי תלמיד
CREATE OR REPLACE VIEW `student_attendance_stats` AS
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

-- סטטיסטיקת נוכחות לפי קורס
CREATE OR REPLACE VIEW `course_attendance_stats` AS
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

-- ========================================
-- נתונים לדוגמה (אופציונלי - למחיקה בפרודקשן)
-- ========================================

-- משתמש לדוגמה: demo@edutrack.com / demo123
-- INSERT INTO `users` (`email`, `password`, `full_name`) VALUES 
-- ('demo@edutrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'מרצה לדוגמה');
