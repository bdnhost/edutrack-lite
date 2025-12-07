# בדיקת תקינות הפלאגין - EduTrack Lite v1.1.0

## ✅ אימות תכונות בקובץ course-detail.php

### טאבים (5):
1. ✅ **סקירה** - Overview
2. ✅ **תלמידים** - Students
3. ✅ **מפגשים מתוכננים** - Lessons (חדש!)
4. ✅ **שיעורים** - Sessions
5. ✅ **דוחות** - Reports

### כפתורים בטאב תלמידים:
- ✅ הוסף תלמיד
- ✅ ייבוא תלמידים (CSV)
- ✅ **הורד תבנית לדוגמה** (חדש!)
- ✅ **תצוגה מקדימה** (חדש!)
- ✅ ייצוא תלמידים
- ✅ **ערוך תלמיד** - כפתור ✏️ בכל שורה (חדש!)

### כפתורים בטאב מפגשים מתוכננים (חדש!):
- ✅ **הוסף מפגש** (btn-add-lesson)
- ✅ **ייבוא מפגשים** (btn-import-lessons)
- ✅ ערוך מפגש - כפתור ✏️ בכל שורה
- ✅ מחק מפגש - כפתור 🗑️ בכל שורה

### מודלים (7):
1. ✅ Add Student Modal
2. ✅ Import Students Modal (עם תבנית ותצוגה מקדימה)
3. ✅ Edit Course Modal
4. ✅ **Edit Student Modal** (חדש!)
5. ✅ **Add Lesson Modal** (חדש!)
6. ✅ **Edit Lesson Modal** (חדש!)

### פונקציות JavaScript חדשות:
- ✅ editStudent()
- ✅ updateStudent()
- ✅ downloadCSVTemplate()
- ✅ previewCSV()
- ✅ loadLessons()
- ✅ displayLessons()
- ✅ saveLesson()
- ✅ editLesson()
- ✅ updateLesson()
- ✅ deleteLesson()

---

## 🔧 בדיקת קובץ ה-ZIP

```bash
# בדוק שה-ZIP מכיל את הקובץ המעודכן:
unzip -l edutrack-lite-wp.zip | grep course-detail
# תוצאה: 57949 bytes (57KB)

# בדוק שיש בו את הטאב מפגשים:
unzip -p edutrack-lite-wp.zip admin/pages/course-detail.php | grep "מפגשים מתוכננים"
# תוצאה: 3 מופעים

# בדוק כפתורים:
unzip -p edutrack-lite-wp.zip admin/pages/course-detail.php | grep -E "btn-add-lesson|btn-import-lessons"
# תוצאה: נמצאו!
```

---

## 🚨 בעיה נפוצה: וורדפרס לא מעדכן קבצים

### הסיבה:
כאשר אתה מעדכן פלאגין בוורדפרס, לפעמים הקבצים הישנים לא נמחקים!

### הפתרון המלא:

#### שלב 1: מחק את הפלאגין הישן לגמרי
```
1. היכנס לוורדפרס → תוספים
2. כבה את EduTrack Lite
3. לחץ מחק (לא רק כיבוי!)
4. וודא שהתיקייה נמחקה:
   wp-content/plugins/edutrack-lite-wp/
```

#### שלב 2: נקה Cache
```
1. נקה cache של הדפדפן (Ctrl+Shift+Delete)
2. אם יש לך תוסף Cache (WP Super Cache/W3 Total Cache):
   - נקה את ה-cache של וורדפרס
3. נקה cache של השרת אם אפשרי
```

#### שלב 3: התקן את הגרסה החדשה
```
1. וורדפרס → תוספים → הוסף חדש
2. העלה תוסף → בחר edutrack-lite-wp.zip
3. התקן עכשיו
4. הפעל
```

#### שלב 4: אמת שהכל עובד
```
1. EduTrack → קורסים
2. פתח קורס (או צור חדש)
3. אמת שיש 5 טאבים:
   ✅ סקירה
   ✅ תלמידים
   ✅ מפגשים מתוכננים ← זה חייב להיות!
   ✅ שיעורים
   ✅ דוחות
4. לחץ על "תלמידים" - אמת כפתורים:
   ✅ הורד תבנית לדוגמה
   ✅ תצוגה מקדימה
5. לחץ על "מפגשים מתוכננים":
   ✅ הוסף מפגש
   ✅ ייבוא מפגשים
```

---

## 📊 מה יש בגרסה 1.1.0

### Backend (AJAX Handlers):
1. ✅ edutrack_update_student
2. ✅ edutrack_get_lessons
3. ✅ edutrack_add_lesson
4. ✅ edutrack_update_lesson
5. ✅ edutrack_delete_lesson

### Database:
```sql
-- טבלת lessons עם שדות חדשים:
duration INT(11) DEFAULT 90
status VARCHAR(20) DEFAULT 'planned'
```

### קבצים:
- ✅ edutrack-lite.php (v1.1.0)
- ✅ class-edutrack-database.php (עם migration)
- ✅ class-edutrack-activator.php (עם run_migrations)
- ✅ class-edutrack-ajax.php (5 handlers חדשים)
- ✅ class-edutrack.php (רישום handlers)
- ✅ course-detail.php (1406 שורות!)

---

## 🐛 אם עדיין לא רואה את השינויים:

### בדיקה 1: וודא שהקובץ התעדכן
```bash
# SSH לשרת:
ls -lh wp-content/plugins/edutrack-lite-wp/admin/pages/course-detail.php

# צריך להיות: 57KB, 1406 שורות
wc -l wp-content/plugins/edutrack-lite-wp/admin/pages/course-detail.php
```

### בדיקה 2: בדוק אם יש שגיאות JavaScript
```
1. פתח דף הקורס
2. לחץ F12 (Developer Tools)
3. לחץ Console
4. חפש שגיאות אדומות
```

### בדיקה 3: וודא שה-AJAX handlers נרשמו
```bash
# בדוק בקובץ:
grep -n "edutrack_add_lesson" wp-content/plugins/edutrack-lite-wp/includes/class-edutrack.php

# צריך להיות בשורות 109-112
```

### בדיקה 4: אמת גרסת הפלאגין
```
1. וורדפרס → תוספים
2. חפש "EduTrack Lite"
3. צריך להיות כתוב: "גרסה 1.1.0"
```

---

## 📝 רשימת שינויים מלאה v1.1.0

### תכונות חדשות:
1. ✅ עריכת תלמיד - מודל מלא עם ולידציה
2. ✅ הורדת תבנית CSV - קובץ לדוגמה להורדה
3. ✅ תצוגה מקדימה CSV - אימות לפני ייבוא
4. ✅ ניהול מפגשים מתוכננים - CRUD מלא
   - הוספה
   - עריכה
   - מחיקה
   - תאריך ושעה
   - משך (ברירת מחדל 90 דקות)
   - סטטוס (מתוכנן/הושלם/בוטל)

### תיקונים:
5. ✅ טבלת lessons - הוספת duration ו-status
6. ✅ מערכת migration - עדכון אוטומטי למסד נתונים

---

## 💡 טיפים להתקנה נכונה:

1. **השתמש בדפדפן Incognito** - למניעת בעיות cache
2. **כבה את כל תוספי ה-Cache** - לפני ההתקנה
3. **מחק את התיקייה הישנה ידנית** - אם אפשר דרך FTP/SSH
4. **אל תשתמש ב"עדכן"** - תמיד מחק והתקן מחדש
5. **בדוק הרשאות קבצים** - 644 לקבצים, 755 לתיקיות

---

## ✅ אימות סופי

אם אתה רואה את הדברים הבאים - **הפלאגין עובד**:

```
בעמוד קורס:
├── טאבים (5)
│   ├── סקירה ✅
│   ├── תלמידים ✅
│   │   ├── כפתור הורד תבנית ✅
│   │   ├── כפתור תצוגה מקדימה ✅
│   │   └── כפתור ✏️ בכל תלמיד ✅
│   ├── מפגשים מתוכננים ✅ ← זה הכי חשוב!
│   │   ├── הוסף מפגש ✅
│   │   └── ייבוא מפגשים ✅
│   ├── שיעורים ✅
│   └── דוחות ✅
```

אם אתה **לא רואה** את "מפגשים מתוכננים" - הקבצים לא התעדכנו!
