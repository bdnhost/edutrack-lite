<?php
// בדיקת סשן מתקדמת
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html dir='rtl'><head><meta charset='UTF-8'><title>בדיקת סשן</title>";
echo "<style>body{font-family:Arial;padding:20px;direction:rtl;}";
echo ".success{color:green;font-weight:bold;}";
echo ".error{color:red;font-weight:bold;}";
echo ".info{color:blue;}";
echo "pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;direction:ltr;text-align:left;}";
echo "</style></head><body>";

echo "<h1>🔍 בדיקת סשן מתקדמת - EduTrack Lite</h1>";
echo "<hr>";

// 1. בדיקת קובץ config
echo "<h2>1️⃣ בדיקת קובץ Config</h2>";
if (file_exists('config.php')) {
    echo "<p class='success'>✅ קובץ config.php קיים</p>";
    require_once 'config.php';
} else {
    echo "<p class='error'>❌ קובץ config.php לא נמצא!</p>";
    exit;
}

// 2. בדיקת חיבור למסד נתונים
echo "<h2>2️⃣ בדיקת חיבור למסד נתונים</h2>";
if (isset($conn) && $conn->ping()) {
    echo "<p class='success'>✅ חיבור למסד נתונים תקין</p>";
    echo "<p class='info'>Database: " . DB_NAME . "</p>";
} else {
    echo "<p class='error'>❌ אין חיבור למסד נתונים</p>";
}

// 3. בדיקת סשן
echo "<h2>3️⃣ מידע על Session</h2>";
echo "<table border='1' cellpadding='5' style='direction:ltr;text-align:left;'>";
echo "<tr><th>פרמטר</th><th>ערך</th></tr>";
echo "<tr><td>Session ID</td><td>" . session_id() . "</td></tr>";
echo "<tr><td>Session Name</td><td>" . session_name() . "</td></tr>";
echo "<tr><td>Session Status</td><td>" . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NONE') . "</td></tr>";
echo "<tr><td>Session Save Path</td><td>" . session_save_path() . "</td></tr>";
echo "</table>";

// 4. Cookie Parameters
echo "<h2>4️⃣ הגדרות Cookie</h2>";
$params = session_get_cookie_params();
echo "<pre>" . print_r($params, true) . "</pre>";

// 5. Session Data
echo "<h2>5️⃣ נתוני Session</h2>";
if (!empty($_SESSION)) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";

    if (isset($_SESSION['user_id'])) {
        echo "<p class='success'>✅ user_id קיים בסשן: " . $_SESSION['user_id'] . "</p>";

        // בדוק אם המשתמש קיים במסד נתונים
        $stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            echo "<p class='success'>✅ משתמש נמצא במסד נתונים:</p>";
            echo "<pre>" . print_r($user, true) . "</pre>";
        } else {
            echo "<p class='error'>❌ משתמש לא נמצא במסד נתונים!</p>";
        }
    } else {
        echo "<p class='error'>❌ user_id לא קיים בסשן</p>";
    }
} else {
    echo "<p class='error'>❌ Session ריק - אין נתונים</p>";
}

// 6. Cookies
echo "<h2>6️⃣ Cookies</h2>";
if (!empty($_COOKIE)) {
    echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
} else {
    echo "<p class='error'>❌ אין Cookies</p>";
}

// 7. PHP Info
echo "<h2>7️⃣ מידע PHP</h2>";
echo "<table border='1' cellpadding='5' style='direction:ltr;text-align:left;'>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>session.cookie_httponly</td><td>" . ini_get('session.cookie_httponly') . "</td></tr>";
echo "<tr><td>session.cookie_secure</td><td>" . ini_get('session.cookie_secure') . "</td></tr>";
echo "<tr><td>session.cookie_samesite</td><td>" . ini_get('session.cookie_samesite') . "</td></tr>";
echo "<tr><td>session.gc_maxlifetime</td><td>" . ini_get('session.gc_maxlifetime') . " seconds</td></tr>";
echo "<tr><td>session.cookie_lifetime</td><td>" . ini_get('session.cookie_lifetime') . " seconds</td></tr>";
echo "<tr><td>session.cookie_path</td><td>" . ini_get('session.cookie_path') . "</td></tr>";
echo "<tr><td>session.cookie_domain</td><td>" . ini_get('session.cookie_domain') . "</td></tr>";
echo "</table>";

// 8. בדיקת HTTPS
echo "<h2>8️⃣ בדיקת HTTPS</h2>";
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
if ($is_https) {
    echo "<p class='success'>✅ האתר רץ על HTTPS</p>";
} else {
    echo "<p class='error'>⚠️ האתר לא רץ על HTTPS</p>";
    echo "<p class='info'>אם SECURE_SESSION=true בconfig, זה יכול לגרום לבעיות</p>";
}
echo "<p class='info'>SECURE_SESSION בconfig: " . (SECURE_SESSION ? 'true' : 'false') . "</p>";

// 9. בדיקת טבלת courses
echo "<h2>9️⃣ בדיקת טבלת courses</h2>";
$result = $conn->query("DESCRIBE courses");
$has_lecturer_id = false;
$has_user_id = false;

while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'lecturer_id')
        $has_lecturer_id = true;
    if ($row['Field'] === 'user_id')
        $has_user_id = true;
}

if ($has_lecturer_id) {
    echo "<p class='success'>✅ השדה lecturer_id קיים</p>";
} else {
    echo "<p class='error'>❌ השדה lecturer_id לא קיים</p>";
}

if ($has_user_id) {
    echo "<p class='error'>⚠️ השדה user_id עדיין קיים (צריך להיות lecturer_id)</p>";
}

// 10. המלצות
echo "<h2>🔧 המלצות</h2>";
echo "<ul>";

if (!$has_lecturer_id) {
    echo "<li class='error'>הרץ את database/FULL_MIGRATION.sql</li>";
}

if (empty($_SESSION)) {
    echo "<li class='error'>Session ריק - נסה להתחבר שוב</li>";
}

if (SECURE_SESSION && !$is_https) {
    echo "<li class='error'>שנה SECURE_SESSION ל-false בconfig.php (שורה 15)</li>";
}

if (empty($_COOKIE)) {
    echo "<li class='error'>אין Cookies - בדוק שהדפדפן מאפשר cookies</li>";
}

echo "</ul>";

echo "<hr>";
echo "<h3>📋 צעדים הבאים:</h3>";
echo "<ol>";
echo "<li>אם Session ריק - נסה להתחבר ב-<a href='../login.html'>login.html</a></li>";
echo "<li>אחרי התחברות, רענן את הדף הזה</li>";
echo "<li>אם עדיין יש בעיה - העתק את כל המידע ושלח לתמיכה</li>";
echo "</ol>";

echo "</body></html>";
?>