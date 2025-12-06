# 🔧 הוראות התקנת מסד נתונים - EduTrack Lite

## ⚠️ בעיה נוכחית
המערכת לא מחוברת למסד נתונים, ולכן מקבלים שגיאות 500.

---

## 📋 שלב 1: יצירת מסד נתונים ב-cPanel

1. **היכנס ל-cPanel** של bdnhost.net
2. **לך ל-MySQL® Databases**
3. **צור מסד נתונים חדש:**
   - שם: `edutrack_lite` (או כל שם אחר)
   - לחץ **Create Database**
   - שים לב: השם המלא יהיה כנראה `bdnhost_edutrack_lite`

4. **צור משתמש חדש:**
   - Username: `edutrack_user` (או כל שם)
   - Password: צור סיסמה חזקה
   - לחץ **Create User**

5. **הוסף משתמש למסד נתונים:**
   - בחר את המשתמש שיצרת
   - בחר את מסד הנתונים שיצרת
   - לחץ **Add**
   - **תן הרשאות מלאות (ALL PRIVILEGES)**
   - לחץ **Make Changes**

---

## 📝 שלב 2: עדכון config.php

ערוך את הקובץ: `api/config.php`

שנה את השורות הבאות:

```php
// הגדרות מסד נתונים
define('DB_HOST', 'localhost');              // ← השאר localhost (בדרך כלל)
define('DB_USER', 'bdnhost_edutrack_user');  // ← שנה לשם המשתמש שיצרת
define('DB_PASS', 'your_strong_password');   // ← שנה לסיסמה שיצרת
define('DB_NAME', 'bdnhost_edutrack_lite');  // ← שנה לשם מסד הנתונים המלא
```

**חשוב:** שמור את הקובץ!

---

## 🗄️ שלב 3: ייבוא הטבלאות

### אפשרות א': דרך phpMyAdmin (מומלץ)

1. ב-cPanel, לך ל-**phpMyAdmin**
2. בחר את מסד הנתונים שיצרת (bdnhost_edutrack_lite)
3. לחץ על **Import** בתפריט העליון
4. לחץ **Choose File** והעלה: `database/schema.sql`
5. לחץ **Go** בתחתית העמוד
6. אמור לראות הודעה: **Import has been successfully finished**

### אפשרות ב': דרך SSH (אם יש גישה)

```bash
mysql -u bdnhost_edutrack_user -p bdnhost_edutrack_lite < database/schema.sql
```

---

## ✅ שלב 4: בדיקת החיבור

### בדיקה מהירה:
גש לכתובת: `https://bdnhost.net/edutrack-lite/api/test_connection.php`

אתה אמור לראות:
```
✓ config.php exists
✓ config.php loaded successfully
✓ Database connected successfully
✓ MySQL version: X.X.X
✓ Table 'users' exists
✓ Table 'institutions' exists
...
=== ALL TESTS PASSED ===
```

### אם יש שגיאה:
- **"Access denied"** → בדוק שם משתמש/סיסמה ב-config.php
- **"Unknown database"** → בדוק את שם מסד הנתונים ב-config.php
- **"No such file or directory"** → נסה לשנות DB_HOST ל-`127.0.0.1` במקום `localhost`

---

## 🎯 שלב 5: בדיקת Login

1. גש ל: `https://bdnhost.net/edutrack-lite/login.html`
2. לחץ על **הרשמה**
3. מלא פרטים:
   - אימייל: `test@example.com`
   - סיסמה: `123456`
   - שם מלא: `משתמש ניסיון`
4. לחץ **הרשם**

אם הכל עובד, תועבר לדשבורד!

---

## 🐛 פתרון בעיות נפוצות

### שגיאה: "Access denied for user"
**פתרון:** בדוק שהמשתמש הוסף למסד הנתונים עם הרשאות מלאות ב-cPanel

### שגיאה: "Unknown database"
**פתרון:** בדוק ב-phpMyAdmin שמסד הנתונים קיים ושהשם תואם ל-config.php

### שגיאה: "Table doesn't exist"
**פתרון:** ייבא את schema.sql דרך phpMyAdmin

### שגיאה: "No such file or directory"
**פתרון 1:** שנה ב-config.php:
```php
define('DB_HOST', '127.0.0.1');  // במקום localhost
```

**פתרון 2:** שנה ל:
```php
define('DB_HOST', 'localhost:/tmp/mysql.sock');
```

---

## 🔐 אבטחה

לאחר שהמערכת עובדת:

1. **מחק קבצי debug:**
   ```bash
   rm api/test_connection.php
   rm api/test_db_params.php
   ```

2. **ודא שconfig.php לא ב-git:**
   - זה כבר מוגדר ב-.gitignore ✅

3. **שנה SECURE_SESSION ל-true:**
   - זה כבר מוגדר ב-config.php ✅

---

## 📞 צריך עזרה?

אם אחרי כל זה עדיין לא עובד, שלח לי:
1. Screenshot של phpMyAdmin המראה את הטבלאות
2. התוכן של `api/test_connection.php` (הפלט)
3. Screenshot של שגיאת הקונסול בדפדפן

---

**תאריך יצירה:** 2025-12-06
**גרסה:** 1.0
