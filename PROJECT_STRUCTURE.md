# EduTrack Lite - מבנה הפרויקט

## 📁 מבנה הקבצים

```
edutrack-lite/
│
├── 📄 index.html              # דף הבית - Dashboard
├── 📄 login.html              # דף התחברות והרשמה
├── 📄 course.html             # פרטי קורס + ניהול תלמידים
├── 📄 session.html            # מסך השיעור עם QR Code (למרצה)
├── 📄 attend.html             # דף רישום נוכחות (לתלמיד)
├── 📄 install.php             # בדיקת דרישות התקנה
├── 📄 .htaccess               # הגדרות Apache + אבטחה
├── 📄 .gitignore              # Git ignore
├── 📄 README.md               # מדריך מפורט
├── 📄 CHANGELOG.md            # רשימת שינויים
├── 📄 TODO.md                 # משימות עתידיות
│
├── 📂 api/                    # Backend PHP
│   ├── config.php             # הגדרות DB + פונקציות עזר
│   ├── config.sample.php      # תבנית הגדרות
│   ├── auth.php               # התחברות והרשמה
│   ├── courses.php            # CRUD קורסים
│   ├── students.php           # CRUD תלמידים + ייבוא CSV
│   ├── session.php            # התחלת שיעור + QR
│   ├── attend.php             # רישום נוכחות (לתלמיד)
│   └── institutions.php       # ניהול מכללות
│
├── 📂 css/                    # Stylesheets
│   └── style.css              # עיצוב מותאם אישית (RTL)
│
├── 📂 js/                     # JavaScript
│   └── main.js                # פונקציות API + Utilities
│
├── 📂 database/               # SQL
│   └── schema.sql             # מבנה DB + Views
│
└── 📂 uploads/                # תיקיית העלאות (CSV)
    └── .gitkeep
```

---

## 🎯 תכונות עיקריות

### 1. אימות ואבטחה
- ✅ התחברות והרשמה מאובטחת
- ✅ הצפנת סיסמאות (bcrypt)
- ✅ Session management
- ✅ SQL Injection protection
- ✅ XSS protection

### 2. ניהול קורסים
- ✅ יצירת קורסים חדשים
- ✅ שיוך למכללות שונות
- ✅ ניהול סמסטרים
- ✅ ארכיון קורסים

### 3. ניהול תלמידים
- ✅ הוספה ידנית
- ✅ ייבוא מ-CSV
- ✅ אימות מספרי טלפון
- ✅ מניעת כפילויות
- ✅ ייצוא ל-CSV

### 4. מערכת הנוכחות (הלב! ❤️)
- ✅ יצירת QR Code ייחודי לכל שיעור
- ✅ טיימר 30 דקות
- ✅ עדכון בזמן אמת (Auto-refresh)
- ✅ אימות תלמיד לפי טלפון
- ✅ מניעת רישום כפול
- ✅ סגירה אוטומטית בתום הזמן

### 5. דוחות וסטטיסטיקות
- ✅ נוכחות לפי תלמיד
- ✅ נוכחות לפי קורס
- ✅ אחוזי נוכחות
- ✅ סיכומים מהירים

---

## 🔧 טכנולוגיות

### Frontend
- **HTML5** - מבנה דפים סמנטי
- **CSS3** - עיצוב מותאם אישית + RTL
- **Bootstrap 5** - Framework UI רספונסיבי
- **jQuery 3.6** - פשטות ב-AJAX
- **QRCode.js** - יצירת QR Codes
- **Bootstrap Icons** - אייקונים

### Backend
- **PHP 7.4+** - שפת שרת
- **MySQL 5.7+** - מסד נתונים
- **MySQLi** - חיבור למסד
- **Apache** - שרת Web

### אבטחה
- **bcrypt** - הצפנת סיסמאות
- **Prepared Statements** - הגנה מ-SQL Injection
- **htmlspecialchars()** - הגנה מ-XSS
- **HTTPS** - תקשורת מוצפנת (מומלץ)

---

## 📱 ממשקי משתמש

### 1. מרצה
```
login.html → index.html → course.html → session.html
                              ↓
                        (ייבוא תלמידים)
```

### 2. תלמיד
```
[סריקת QR Code] → attend.html → [הזנת טלפון] → [אישור]
```

---

## 💾 מסד הנתונים

### טבלאות
1. **users** - משתמשים (מרצים)
2. **institutions** - מוסדות לימוד
3. **courses** - קורסים
4. **students** - תלמידים
5. **attendance_sessions** - שיעורים פעילים
6. **attendance_records** - רישומי נוכחות

### Views (סטטיסטיקות)
1. **student_attendance_stats** - נוכחות לפי תלמיד
2. **course_attendance_stats** - נוכחות לפי קורס

---

## 🚀 התקנה מהירה

```bash
1. העלה קבצים לשרת
2. צור מסד נתונים (MySQL)
3. ייבא את database/schema.sql
4. ערוך api/config.php
5. גש ל-install.php לבדיקה
6. התחבר דרך login.html
```

---

## 📞 תמיכה

- **מפתח:** יעקב
- **גרסה:** 1.0.0
- **תאריך:** דצמבר 2024
- **רישיון:** MIT

---

## 🎉 הצלחה!

המערכת מוכנה לשימוש. תהנה! 🚀
