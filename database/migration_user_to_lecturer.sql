-- Migration: שינוי user_id ל-lecturer_id בטבלת courses
-- הרץ את הקובץ הזה במסד הנתונים שלך

-- 1. הסר את ה-Foreign Key הישן
ALTER TABLE `courses` DROP FOREIGN KEY `courses_ibfk_1`;

-- 2. שנה את שם העמודה
ALTER TABLE `courses` CHANGE `user_id` `lecturer_id` int(11) NOT NULL;

-- 3. הוסף את ה-Foreign Key מחדש
ALTER TABLE `courses` 
  ADD CONSTRAINT `courses_lecturer_fk` 
  FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- 4. עדכן את האינדקס
ALTER TABLE `courses` DROP INDEX `user_id`;
ALTER TABLE `courses` ADD INDEX `lecturer_id` (`lecturer_id`);

-- בדיקה
SELECT 'Migration completed successfully!' as status;
