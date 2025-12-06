<?php
// Database Connection Diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 בדיקת פרמטרי חיבור למסד נתונים</h2>";

// Try to find database credentials from environment or cPanel
echo "<h3>1️⃣ מידע על הסביבה:</h3>";
echo "Server: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

echo "<h3>2️⃣ אפשרויות חיבור למסד נתונים:</h3>";

// Common database hosts on shared hosting
$possible_hosts = [
    'localhost',
    '127.0.0.1',
    'localhost:/tmp/mysql.sock',
    'localhost:/var/lib/mysql/mysql.sock',
    'mysql',
    'db'
];

echo "<p>נסה את המארחים הבאים:</p><ul>";
foreach ($possible_hosts as $host) {
    echo "<li><code>$host</code></li>";
}
echo "</ul>";

echo "<h3>3️⃣ הוראות תיקון:</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>עליך לערוך את הקובץ:</strong> <code>api/config.php</code></p>";
echo "<p>שנה את השורות הבאות בהתאם לפרטי cPanel שלך:</p>";
echo "<pre style='background: #fff; padding: 10px; border-radius: 3px;'>";
echo "define('DB_HOST', 'localhost');  // ← שנה למארח הנכון\n";
echo "define('DB_USER', 'your_username');  // ← שנה לשם משתמש מ-cPanel\n";
echo "define('DB_PASS', 'your_password');  // ← שנה לסיסמה מ-cPanel\n";
echo "define('DB_NAME', 'edutrack_lite');  // ← שנה לשם DB מ-cPanel\n";
echo "</pre>";
echo "</div>";

echo "<h3>4️⃣ איפה למצוא את הפרטים ב-cPanel:</h3>";
echo "<ol>";
echo "<li>היכנס ל-<strong>cPanel</strong></li>";
echo "<li>לך ל-<strong>MySQL Databases</strong></li>";
echo "<li>צור מסד נתונים חדש אם אין (למשל: <code>bdnhost_edutrack</code>)</li>";
echo "<li>צור משתמש למסד הנתונים</li>";
echo "<li>הוסף את המשתמש למסד הנתונים עם הרשאות מלאות</li>";
echo "<li>העתק את הפרטים ל-<code>api/config.php</code></li>";
echo "</ol>";

echo "<h3>5️⃣ ייבוא הטבלאות:</h3>";
echo "<p>לאחר שמסד הנתונים מחובר, ייבא את הסכמה:</p>";
echo "<ol>";
echo "<li>ב-cPanel, לך ל-<strong>phpMyAdmin</strong></li>";
echo "<li>בחר את מסד הנתונים שיצרת</li>";
echo "<li>לחץ על <strong>Import</strong></li>";
echo "<li>העלה את הקובץ: <code>database/schema.sql</code></li>";
echo "<li>לחץ <strong>Go</strong></li>";
echo "</ol>";

echo "<h3>6️⃣ בדיקה:</h3>";
echo "<p>לאחר העדכון, גש ל: <a href='test_connection.php'>test_connection.php</a> לבדוק את החיבור</p>";

echo "<hr>";
echo "<p><em>הקובץ הזה נמצא ב-gitignore ולא יועלה לגיט. מחק אותו לאחר התיקון.</em></p>";
?>
