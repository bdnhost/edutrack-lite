# הוראות התקנה - EduTrack Lite

## שלב 1: הגדרת מסד נתונים

### אם זה התקנה חדשה:
1. פתח את phpMyAdmin או MySQL CLI
2. צור מסד נתונים חדש בשם `shlomion_EduTrack`
3. ייבא את הקובץ `database/schema.sql`

### אם המערכת כבר קיימת:
הרץ את קובץ ה-migration המלא:

**דרך phpMyAdmin:**
1. פתח phpMyAdmin
2. בחר את מסד הנתונים
3. לחץ על "SQL"
4. העתק את התוכן של `database/FULL_MIGRATION.sql`
5. הדבק ולחץ "Go"

**דרך MySQL CLI:**
```bash
mysql -u shlomion_EduTrack1 -p shlomion_EduTrack < database/FULL_MIGRATION.sql
```

הסקריפט מבצע:
- ✅ שינוי user_id → lecturer_id
- ✅ יצירת טבלת lessons
- ✅ הוספת lesson_id ל-attendance_sessions
- ✅ יצירת כל ה-Views

## שלב 2: הגדרת קובץ config.php

הקובץ `api/config.php` כבר קיים עם ההגדרות הנכונות:
- DB_HOST: localhost
- DB_USER: shlomion_EduTrack1
- DB_PASS: iZvzA1Vgr0STZCUD
- DB_NAME: shlomion_EduTrack
- SITE_URL: https://bdnhost.net/edutrack-lite/

## שלב 3: בדיקת הרשאות

וודא שלתיקיות הבאות יש הרשאות כתיבה:
```bash
chmod 755 uploads/
chmod 755 database/
```

## שלב 4: בדיקת התקנה

1. גש ל: `https://bdnhost.net/edutrack-lite/`
2. לחץ על "הירשם כאן"
3. צור משתמש חדש
4. אם ההתחברות מצליחה - המערכת פועלת!

## בדיקת בעיות

### אם המשתמש נזרק חזרה ללוגאין:

1. בדוק את הלוגים של PHP:
```bash
tail -f /path/to/php/error.log
```

2. בדוק את הסשן:
```
https://bdnhost.net/edutrack-lite/api/debug_session.php
```

3. וודא שהטבלה `users` קיימת:
```sql
SHOW TABLES LIKE 'users';
```

4. וודא שהשדה `lecturer_id` קיים בטבלה `courses`:
```sql
DESCRIBE courses;
```

### בעיות נפוצות:

**שגיאה: "קורס לא נמצא"**
- הרץ את `migration_user_to_lecturer.sql`

**שגיאה: "Unknown column 'lesson_id'"**
- הרץ את `migration_add_lessons.sql`

**שגיאה: "Table 'users' doesn't exist"**
- ייבא את `database/schema.sql` מחדש

## תיקונים שבוצעו

1. ✅ נוצר קובץ `api/config.php` עם הפרטים הנכונים
2. ✅ תוקנו כל השדות מ-`user_id` ל-`lecturer_id`
3. ✅ הוארך זמן הסשן ל-24 שעות
4. ✅ תוקנה הפונקציה `requireAuth()` ב-`main.js`
5. ✅ עודכנו `index.html` ו-`session.html` להשתמש ב-callback
6. ✅ נוספה טבלת `lessons` לתמיכה במפגשים
7. ✅ עודכנה טבלת `attendance_sessions` לתמוך בקישור למפגשים

## קבצים שנוצרו/עודכנו:

- `api/config.php` - קובץ קונפיגורציה חדש
- `api/debug_session.php` - כלי בדיקה לסשן
- `database/migration_user_to_lecturer.sql` - migration לשינוי user_id
- `database/migration_add_lessons.sql` - migration להוספת lessons
- `database/schema.sql` - עודכן עם השינויים
- `INSTALLATION.md` - מסמך זה

## תמיכה

אם יש בעיות נוספות, בדוק:
1. לוגים של PHP
2. לוגים של MySQL
3. Console של הדפדפן (F12)
