-- Migration: הוספת טבלת lessons ועדכון attendance_sessions
-- הרץ את הקובץ הזה במסד הנתונים שלך

-- 1. צור טבלת lessons
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

-- 2. הוסף עמודת lesson_id לטבלת attendance_sessions
ALTER TABLE `attendance_sessions` 
  ADD COLUMN `lesson_id` int(11) DEFAULT NULL COMMENT 'קישור למפגש ספציפי (אופציונלי)' AFTER `course_id`;

-- 3. הוסף Foreign Key
ALTER TABLE `attendance_sessions` 
  ADD CONSTRAINT `attendance_sessions_lesson_fk` 
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

-- 4. הוסף אינדקס
ALTER TABLE `attendance_sessions` ADD INDEX `lesson_id` (`lesson_id`);

-- בדיקה
SELECT 'Migration completed successfully!' as status;
