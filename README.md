# EduTrack Lite
### מערכת ניהול נוכחות חכמה עם QR Code

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)

---

## 📋 תיאור

EduTrack Lite היא מערכת פשוטה ויעילה לניהול נוכחות תלמידים באמצעות QR Code.
המערכת מאפשרת למרצים לפתוח שיעור, להציג QR Code, ולראות בזמן אמת מי נרשם לנוכחות.

### ✨ תכונות עיקריות

- ✅ **התחברות והרשמה** - מערכת אימות מאובטחת
- 🎓 **ניהול קורסים** - יצירה ועריכה של קורסים
- 👥 **ניהול תלמידים** - הוספה ידנית או ייבוא מ-CSV
- 📱 **QR Code** - תלמידים סורקים ונרשמים אוטומטית
- ⏱️ **טיימר** - 30 דקות לסריקת QR
- 📊 **דוחות** - סטטיסטיקות נוכחות
- 🌐 **RTL** - תמיכה מלאה בעברית

---

## 🚀 התקנה

### דרישות מערכת

- PHP 7.4 או גרסה גבוהה יותר
- MySQL 5.7 או גרסה גבוהה יותר
- Apache/Nginx עם mod_rewrite
- שרת עם תמיכה ב-HTTPS (מומלץ)

### שלב 1: העלאת קבצים

1. **הורד את כל הקבצים** לשרת שלך (cPanel / FTP)
2. **העלה** את כל התיקיות והקבצים ל-`public_html` או לתיקייה אחרת

המבנה צריך להיראות כך:
```
your-domain.com/
├── api/
│   ├── config.php
│   ├── auth.php
│   ├── courses.php
│   ├── students.php
│   ├── session.php
│   └── attend.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── database/
│   └── schema.sql
├── index.html
├── login.html
├── course.html
├── session.html
└── attend.html
```

### שלב 2: יצירת מסד נתונים

1. **היכנס ל-phpMyAdmin** דרך cPanel
2. **צור מסד נתונים חדש**:
   - שם: `edutrack_lite` (או שם אחר לבחירתך)
   - Collation: `utf8mb4_unicode_ci`

3. **ייבא את הסכמה**:
   - לחץ על מסד הנתונים שיצרת
   - לחץ על "Import" / "ייבוא"
   - בחר את הקובץ `database/schema.sql`
   - לחץ "Go"

### שלב 3: הגדרת חיבור למסד נתונים

הקובץ `api/config.php` כבר קיים עם ההגדרות. אם צריך לשנות, ערוך:

```php
// שורות 7-10
define('DB_HOST', 'localhost');           // כתובת שרת MySQL
define('DB_USER', 'your_username');       // שם משתמש MySQL שלך
define('DB_PASS', 'your_password');       // סיסמה שלך
define('DB_NAME', 'edutrack_lite');       // שם מסד הנתונים שיצרת
```

**חשוב:** שנה גם את `SITE_URL` (שורה 13):
```php
define('SITE_URL', 'https://your-domain.com');
```

### שלב 3.5: הרצת Migrations (אם המערכת כבר הייתה מותקנת)

אם המערכת כבר הייתה מותקנת קודם, הרץ את הבדיקה:
```
https://your-domain.com/api/test_db.php
```

אם הבדיקה מראה שצריך migrations, הרץ דרך phpMyAdmin:
1. `database/migration_user_to_lecturer.sql`
2. `database/migration_add_lessons.sql`

ראה `INSTALLATION.md` לפרטים נוספים.

### שלב 4: הגדרת הרשאות

וודא שלתיקיות יש הרשאות כתיבה (דרך FTP או cPanel):
```bash
chmod 755 api/
chmod 755 uploads/
```

---

## 📱 שימוש

### 1. הרשמה והתחברות

- גש ל: `https://your-domain.com/login.html`
- לחץ על "הירשם כאן"
- מלא את הפרטים וצור חשבון

### 2. יצירת קורס

- בדשבורד, לחץ "קורס חדש"
- מלא את פרטי הקורס (שם, מכללה, סמסטר)
- לחץ "צור קורס"

### 3. הוספת תלמידים

**אופציה א': הוספה ידנית**
- היכנס לקורס
- לחץ "הוסף תלמיד"
- מלא את הפרטים

**אופציה ב': ייבוא מ-CSV**
- היכנס לקורס
- לחץ על טאב "ייבוא"
- הורד תבנית לדוגמה
- מלא את הקובץ ועלה אותו

פורמט CSV:
```csv
שם פרטי,שם משפחה,טלפון,אימייל
יוסי,כהן,0501234567,yossi@example.com
מיכל,לוי,0529876543,michal@example.com
```

### 4. התחלת שיעור

- היכנס לקורס
- לחץ "התחל שיעור"
- **QR Code יוצג על המסך**
- הקרין את המסך או הצג בנייד

### 5. רישום נוכחות (תלמיד)

התלמיד:
1. סורק את ה-QR Code
2. נפתח דף ברמקול / טאבלט
3. מזין מספר טלפון (10 ספרות)
4. לוחץ "אשר נוכחות"
5. מקבל אישור ✓

---

## 🔧 הגדרות מתקדמות

### שינוי זמן תפוגת שיעור

בקובץ `api/config.php`, שורה 14:
```php
define('SESSION_TIMEOUT', 30); // דקות (ברירת מחדל: 30)
```

### הוספת מכללות

בקובץ `database/schema.sql`, הוסף שורות:
```sql
INSERT INTO `institutions` (`name`) VALUES ('מכללת....');
```

או דרך phpMyAdmin:
1. טבלה `institutions`
2. Insert
3. הוסף שם מכללה

### HTTPS (מומלץ מאוד!)

1. התקן תעודת SSL (Let's Encrypt חינם בcPanel)
2. בקובץ `api/config.php`, שנה:
```php
define('SECURE_SESSION', true); // שורה 15
```

---

## 🐛 פתרון בעיות

### המשתמש נזרק חזרה ללוגאין

**פתרון:**
1. הרץ `api/test_db.php` לבדיקה
2. אם צריך migrations - הרץ אותם (ראה `INSTALLATION.md`)
3. בדוק `api/debug_session.php` לבדיקת סשן
4. נקה cache של הדפדפן
5. וודא שקוקיז מאופשרים

### שגיאה: "Connection failed"

**פתרון:**
- בדוק שפרטי החיבור ב-`config.php` נכונים
- וודא שמסד הנתונים קיים
- בדוק שהמשתמש יש לו הרשאות

### שגיאה: "קורס לא נמצא"

**פתרון:**
- הרץ `database/migration_user_to_lecturer.sql`
- זו בעיה של שדה `user_id` vs `lecturer_id`

### QR Code לא עובד

**פתרון:**
- וודא ש-`SITE_URL` ב-`config.php` נכון
- בדוק שהשרת נגיש מהאינטרנט
- נסה מדפדפן אחר

### תלמיד מקבל "מספר לא נמצא"

**פתרון:**
- וודא שהטלפון במערכת תקין (10 ספרות, מתחיל ב-0)
- בדוק שהתלמיד שייך לקורס
- נסה להוסיף אותו שוב

### ייבוא CSV נכשל

**פתרון:**
- וודא שהקובץ ב-UTF-8 encoding
- בדוק שהעמודות בסדר הנכון
- מספרי טלפון חייבים להיות 10 ספרות

---

## 📊 מבנה מסד הנתונים

### טבלאות עיקריות

1. **users** - משתמשים (מרצים)
2. **institutions** - מוסדות לימוד
3. **courses** - קורסים
4. **students** - תלמידים
5. **attendance_sessions** - שיעורים (עם QR)
6. **attendance_records** - רישומי נוכחות

### קשרים

```
users ──< courses ──< students
                 ──< attendance_sessions ──< attendance_records
```

---

## 🔐 אבטחה

- סיסמאות מוצפנות ב-`password_hash()`
- Session מאובטח
- SQL Injection מוגן ב-Prepared Statements
- XSS מוגן ב-`htmlspecialchars()`
- Validation על כל קלט
- Rate limiting מומלץ להוסיף

---

## 📞 תמיכה

- יצרן: יעקב
- גרסה: 1.0.0
- תאריך: דצמבר 2024

---

## 📝 רישיון

MIT License - חופשי לשימוש אישי ומסחרי

---

## 🎯 תכונות עתידיות (רעיונות)

- [ ] שליחת SMS אוטומטי לנעדרים
- [ ] אפליקציית מובייל native
- [ ] אינטגרציה עם Moodle
- [ ] ייצוא לExcel מפורט
- [ ] גרפים אינטראקטיביים
- [ ] התראות WhatsApp
- [ ] סריקת QR מהמצלמה ישירות

---

## 🙏 תודות

- Bootstrap 5 - UI Framework
- jQuery - JavaScript Library
- QRCode.js - QR Code Generation
- Bootstrap Icons - Icons

---

**בהצלחה! 🚀**
