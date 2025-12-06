<?php
// EduTrack Lite - Database Configuration SAMPLE
// העתק קובץ זה ל-config.php וערוך את הפרטים

// הגדרות מסד נתונים
define('DB_HOST', 'localhost');              // כתובת שרת MySQL
define('DB_USER', 'your_database_username'); // שם משתמש MySQL
define('DB_PASS', 'your_database_password'); // סיסמת MySQL
define('DB_NAME', 'edutrack_lite');          // שם מסד הנתונים

// הגדרות מערכת
define('SITE_URL', 'http://localhost/edutrack-lite'); // URL של האתר
define('SESSION_TIMEOUT', 30); // זמן תפוגה של שיעור בדקות (30 = חצי שעה)

// אבטחה
define('SESSION_NAME', 'edutrack_session');
define('SECURE_SESSION', false); // שנה ל-true אם יש לך HTTPS

// Timezone
date_default_timezone_set('Asia/Jerusalem');

// הערות חשובות:
// 1. אל תעלה קובץ זה ל-Git - רק את config.sample.php
// 2. ודא שהקובץ config.php לא נגיש מהאינטרנט
// 3. שנה SECURE_SESSION ל-true אם יש לך SSL
// 4. שמור את פרטי ההתחברות במקום מאובטח
?>
