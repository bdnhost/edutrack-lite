# EduTrack Lite WordPress Plugin - Installation Guide

## התקנה מהירה

### שיטה 1: דרך ממשק WordPress (מומלץ)

1. הורד את כל תיקיית `edutrack-lite-wp`
2. דחוס אותה לקובץ ZIP
3. WordPress Admin → Plugins → Add New → Upload Plugin
4. בחר את ה-ZIP והעלה
5. לחץ Activate

### שיטה 2: FTP/SFTP

1. העלה את תיקיית `edutrack-lite-wp` ל:
   ```
   /wp-content/plugins/edutrack-lite-wp/
   ```

2. היכנס ל-WordPress Admin → Plugins
3. מצא את "EduTrack Lite" ולחץ Activate

## מה קורה בהפעלה?

1. **יצירת טבלאות**: הפלאגין יוצר 6 טבלאות במסד הנתונים:
   - `wp_edutrack_institutions`
   - `wp_edutrack_courses`
   - `wp_edutrack_students`
   - `wp_edutrack_lessons`
   - `wp_edutrack_attendance_sessions`
   - `wp_edutrack_attendance_records`

2. **הוספת תפקידים**:
   - `edutrack_lecturer` - תפקיד חדש למרצים
   - Capabilities נוספות ל-Administrator

3. **הגדרות ברירת מחדל**:
   - Session timeout: 30 דקות
   - 4 מוסדות לימוד ראשוניים

## דרישות מערכת

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- שרת עם הרשאות כתיבה

## אימות ההתקנה

לאחר ההתקנה, בדוק:

1. **תפריט מנהל**:
   - צריך להופיע תפריט "EduTrack" בצד

2. **טבלאות**:
   ```sql
   SHOW TABLES LIKE 'wp_edutrack_%';
   ```
   צריך להציג 6 טבלאות

3. **תפקידים**:
   - Users → Add New → Role
   - צריך להופיע "EduTrack Lecturer"

## הגדרה ראשונית

1. עבור ל: **EduTrack → Settings**
2. הגדר את זמן התפוגה של שיעורים
3. צור קורס ראשון ב: **EduTrack → Courses**

## שימוש ב-Shortcodes

הוסף shortcodes לעמודים:

```
[edutrack_dashboard]
[edutrack_course id="5"]
[edutrack_attend]
```

## הסרת התקנה

אם תמחק את הפלאגין:
- **לא** יימחקו טבלאות ונתונים (אלא אם תגדיר בהגדרות)
- התפקידים יוסרו
- ה-Capabilities יוסרו

כדי למחוק הכל (כולל נתונים):
1. Settings → Delete all data on uninstall (סמן)
2. Plugins → Delete

## תמיכה

אם נתקלת בבעיות:
1. בדוק את ה-logs: `wp-content/debug.log`
2. אפשר WordPress Debug:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
3. פתח Issue ב-GitHub

---

**יעקב | EduTrack Lite v1.0.0**
