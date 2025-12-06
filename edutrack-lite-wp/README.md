# EduTrack Lite - WordPress Plugin
### מערכת ניהול נוכחות חכמה עם QR Code לוורדפרס

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![License](https://img.shields.io/badge/license-GPL--2.0-green)

---

## 📋 תיאור

EduTrack Lite היא מערכת פשוטה ויעילה לניהול נוכחות תלמידים באמצעות QR Code, מותאמת במיוחד לוורדפרס.
המערכת מאפשרת למרצים לפתוח שיעור, להציג QR Code, ולראות בזמן אמת מי נרשם לנוכחות.

### ✨ תכונות עיקריות

- ✅ **אינטגרציה מלאה עם WordPress** - שימוש במשתמשי וורדפרס
- 🎓 **ניהול קורסים** - יצירה ועריכה של קורסים
- 👥 **ניהול תלמידים** - הוספה ידנית או ייבוא מ-CSV
- 📱 **QR Code** - תלמידים סורקים ונרשמים אוטומטית
- ⏱️ **טיימר** - 30 דקות לסריקת QR (ניתן להגדרה)
- 📊 **דוחות** - סטטיסטיקות נוכחות
- 🎨 **Shortcodes** - הוספת פונקציונליות לכל עמוד
- 🌐 **RTL** - תמיכה מלאה בעברית
- 🔒 **אבטחה** - שימוש ב-WordPress capabilities ו-nonces

---

## 🚀 התקנה

### דרישות מערכת

- WordPress 5.0 או גרסה גבוהה יותר
- PHP 7.4 או גרסה גבוהה יותר
- MySQL 5.7 או גרסה גבוהה יותר

### התקנה

#### שיטה 1: העלאה דרך ממשק ניהול WordPress

1. הורד את תיקיית `edutrack-lite-wp` כקובץ ZIP
2. היכנס לממשק הניהול של WordPress
3. עבור ל: **Plugins → Add New → Upload Plugin**
4. בחר את קובץ ה-ZIP והעלה
5. לחץ על **Activate Plugin**

#### שיטה 2: התקנה ידנית דרך FTP

1. העלה את תיקיית `edutrack-lite-wp` ל: `/wp-content/plugins/`
2. היכנס לממשק הניהול של WordPress
3. עבור ל: **Plugins** ולחץ **Activate** ליד EduTrack Lite

### הגדרה ראשונית

לאחר ההתקנה:

1. תופיע תפריט חדש בצד: **EduTrack**
2. עבור ל: **EduTrack → Settings** כדי להגדיר את זמן התפוגה של שיעורים
3. צור משתמש עם תפקיד **EduTrack Lecturer** או השתמש ב-Administrator

---

## 📱 שימוש

### 1. יצירת קורס

- היכנס ל: **EduTrack → Courses**
- לחץ על **הוסף קורס חדש**
- מלא את פרטי הקורס (שם, מכללה, סמסטר)
- לחץ **שמור**

### 2. הוספת תלמידים

**אופציה א': הוספה ידנית**
- היכנס לקורס
- לחץ **הוסף תלמיד**
- מלא את הפרטים

**אופציה ב': ייבוא מ-CSV**
- היכנס לקורס
- לחץ על **ייבוא תלמידים**
- העלה קובץ CSV בפורמט:
```csv
שם פרטי,שם משפחה,טלפון,אימייל
יוסי,כהן,0501234567,yossi@example.com
מיכל,לוי,0529876543,michal@example.com
```

### 3. התחלת שיעור

- היכנס לקורס
- לחץ **התחל שיעור**
- **QR Code יוצג על המסך**
- הקרין את המסך או הצג ב-projector

### 4. רישום נוכחות (תלמיד)

התלמיד:
1. סורק את ה-QR Code
2. נפתח דף ברמקול / טאבלט
3. מזין מספר טלפון (10 ספרות)
4. לוחץ **אשר נוכחות**
5. מקבל אישור ✓

---

## 🎨 Shortcodes

הפלאגין מספק shortcodes להוספת פונקציונליות בעמודים:

### `[edutrack_dashboard]`
הצגת דשבורד למרצה בעמוד ציבורי.

```
[edutrack_dashboard]
```

### `[edutrack_course id="123"]`
הצגת פרטי קורס ספציפי.

```
[edutrack_course id="5"]
```

### `[edutrack_attend]`
דף נוכחות לתלמידים. ה-token יילקח מה-URL אוטומטית.

```
[edutrack_attend]
```

או:

```
[edutrack_attend token="abc123xyz"]
```

### `[edutrack_session id="123"]`
הצגת שיעור פעיל עם QR Code.

```
[edutrack_session id="10"]
```

---

## 👥 תפקידים והרשאות

הפלאגין יוצר תפקיד חדש:

### EduTrack Lecturer
- ניהול קורסים
- ניהול תלמידים
- פתיחת שיעורים
- צפייה בנוכחות

### Administrator
- כל ההרשאות של Lecturer
- גישה להגדרות המערכת
- ניהול מוסדות לימוד

---

## 🔧 הגדרות מתקדמות

### שינוי זמן תפוגת שיעור

עבור ל: **EduTrack → Settings**
שנה את **זמן תפוגה של שיעור** (5-120 דקות)

### הוספת מכללות

ניתן להוסיף מכללות ישירות מ-phpMyAdmin:

```sql
INSERT INTO `wp_edutrack_institutions` (`name`) VALUES ('מכללת החדשה');
```

או דרך הקוד (בקובץ הפלאגין):

```php
global $wpdb;
$wpdb->insert(
    $wpdb->prefix . 'edutrack_institutions',
    array('name' => 'מכללת החדשה')
);
```

---

## 🔐 אבטחה

- **WordPress Nonces** - הגנה מפני CSRF
- **Capabilities** - בדיקת הרשאות לכל פעולה
- **Prepared Statements** - הגנה מפני SQL Injection
- **Sanitization** - ניקוי כל הקלטים
- **Escaping** - הגנה מפני XSS

---

## 🗄️ מבנה מסד הנתונים

הפלאגין יוצר את הטבלאות הבאות:

- `wp_edutrack_institutions` - מוסדות לימוד
- `wp_edutrack_courses` - קורסים
- `wp_edutrack_students` - תלמידים
- `wp_edutrack_lessons` - מפגשים מתוכננים
- `wp_edutrack_attendance_sessions` - שיעורים פעילים
- `wp_edutrack_attendance_records` - רישומי נוכחות

**הערה:** משתמשים מנוהלים דרך טבלת `wp_users` הרגילה של WordPress.

---

## 📂 מבנה הפלאגין

```
edutrack-lite-wp/
├── edutrack-lite.php              # Main plugin file
├── uninstall.php                  # Cleanup on uninstall
├── README.md                      # This file
├── includes/                      # Core classes
│   ├── class-edutrack.php
│   ├── class-edutrack-loader.php
│   ├── class-edutrack-activator.php
│   ├── class-edutrack-i18n.php
│   ├── class-edutrack-database.php
│   ├── class-edutrack-admin.php
│   ├── class-edutrack-public.php
│   └── class-edutrack-ajax.php
├── admin/                         # Admin interface
│   ├── css/
│   ├── js/
│   └── pages/
│       ├── dashboard.php
│       ├── courses.php
│       └── settings.php
├── public/                        # Public interface
│   ├── css/
│   ├── js/
│   └── templates/
│       ├── dashboard.php
│       ├── course.php
│       ├── attend.php
│       └── session.php
└── languages/                     # Translations (future)
```

---

## 🐛 פתרון בעיות

### הפלאגין לא מופיע בתפריט

**פתרון:**
- ודא שהמשתמש שלך הוא Administrator או בעל תפקיד EduTrack Lecturer
- נסה להפעיל מחדש את הפלאגין

### שגיאה בטעינת הדפים

**פתרון:**
- ודא ש-Bootstrap 5 נטען (אם יש קונפליקט עם ערכת העיצוב)
- בדוק את ה-Console בדפדפן לשגיאות JavaScript

### QR Code לא עובד

**פתרון:**
- ודא שה-URL של האתר נכון בהגדרות WordPress
- בדוק שהשרת נגיש מהאינטרנט
- נסה מדפדפן אחר

### תלמיד מקבל "מספר לא נמצא"

**פתרון:**
- ודא שהטלפון במערכת תקין (10 ספרות, מתחיל ב-0)
- בדוק שהתלמיד שייך לקורס
- נסה להוסיף אותו שוב

---

## 🔄 עדכונים וגרסאות

### גרסה 1.0.0 (הנוכחית)
- שחרור ראשוני
- אינטגרציה מלאה עם WordPress
- ניהול קורסים ותלמידים
- מערכת QR Code לנוכחות
- Shortcodes לעמודים ציבוריים

---

## 📞 תמיכה ותרומה

- **GitHub**: [https://github.com/bdnhost/edutrack-lite](https://github.com/bdnhost/edutrack-lite)
- **Issues**: דווח על באגים ב-GitHub Issues
- **יצרן**: יעקב
- **גרסה**: 1.0.0
- **תאריך**: דצמבר 2024

---

## 📝 רישיון

GPL v2 or later - חופשי לשימוש אישי ומסחרי

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 🎯 תכונות עתידיות (רעיונות)

- [ ] שליחת SMS אוטומטי לנעדרים
- [ ] אפליקציית מובייל native
- [ ] אינטגרציה עם Moodle / Google Classroom
- [ ] ייצוא לExcel מפורט
- [ ] גרפים אינטראקטיביים (Chart.js)
- [ ] התראות WhatsApp
- [ ] סריקת QR מהמצלמה ישירות
- [ ] תמיכה רב-לשונית (WPML)
- [ ] Gutenberg blocks

---

## 🙏 תודות

- **WordPress** - CMS Platform
- **Bootstrap 5** - UI Framework
- **QRCode.js** - QR Code Generation
- **Bootstrap Icons** - Icons
- **jQuery** - JavaScript Library

---

**בהצלחה! 🚀**

אם יש שאלות או בעיות, פתח Issue ב-GitHub או צור קשר.
