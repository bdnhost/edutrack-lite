<?php
// תיקון בעלות על קורסים
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

if (!isLoggedIn()) {
    die('Not logged in');
}

$user_id = getUserId();

echo "<h1>תיקון בעלות על קורסים</h1>";
echo "<p>משתמש מחובר: $user_id</p>";
echo "<hr>";

// הצג את כל הקורסים
echo "<h2>כל הקורסים במערכת:</h2>";
$result = $conn->query("SELECT id, name, lecturer_id FROM courses");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>שם</th><th>lecturer_id</th><th>פעולה</th></tr>";

while ($course = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$course['id']}</td>";
    echo "<td>{$course['name']}</td>";
    echo "<td>{$course['lecturer_id']}</td>";

    if ($course['lecturer_id'] != $user_id) {
        echo "<td>";
        echo "<form method='post' style='display:inline;'>";
        echo "<input type='hidden' name='course_id' value='{$course['id']}'>";
        echo "<input type='hidden' name='action' value='fix'>";
        echo "<button type='submit'>תקן - שייך אליי</button>";
        echo "</form>";
        echo "</td>";
    } else {
        echo "<td>✅ שייך אליך</td>";
    }

    echo "</tr>";
}

echo "</table>";

// טיפול בטופס
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'fix') {
    $course_id = $_POST['course_id'];

    $stmt = $conn->prepare("UPDATE courses SET lecturer_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);

    if ($stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
        echo "✅ הקורס $course_id עודכן בהצלחה!";
        echo "</div>";
        echo "<script>setTimeout(() => location.reload(), 1000);</script>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ שגיאה: " . $stmt->error;
        echo "</div>";
    }
}

echo "<hr>";
echo "<h2>או תקן הכל בבת אחת:</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='fix_all'>";
echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;'>";
echo "תקן את כל הקורסים - שייך אותם אליי";
echo "</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'fix_all') {
    $stmt = $conn->prepare("UPDATE courses SET lecturer_id = ? WHERE lecturer_id != ?");
    $stmt->bind_param("ii", $user_id, $user_id);

    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
        echo "✅ $affected קורסים עודכנו בהצלחה!";
        echo "</div>";
        echo "<script>setTimeout(() => location.reload(), 1000);</script>";
    }
}
?>