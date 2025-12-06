<?php
// בדיקת יצירת סשן
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode([
        'error' => 'Not logged in',
        'session_data' => $_SESSION
    ], JSON_PRETTY_PRINT);
    exit;
}

$user_id = getUserId();
$course_id = $_GET['course_id'] ?? 1;

echo json_encode([
    'logged_in' => true,
    'user_id' => $user_id,
    'course_id' => $course_id,
    'test' => 'Starting session test...'
], JSON_PRETTY_PRINT);

// נסה ליצור סשן
try {
    $stmt = $conn->prepare("SELECT id, name FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();

    if (!$course) {
        echo "\n\nCourse not found or doesn't belong to user!";
        echo "\nUser ID: " . $user_id;
        echo "\nCourse ID: " . $course_id;

        // בדוק אם הקורס קיים בכלל
        $stmt = $conn->prepare("SELECT id, name, lecturer_id FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $any_course = $stmt->get_result()->fetch_assoc();

        if ($any_course) {
            echo "\n\nCourse exists but lecturer_id doesn't match:";
            echo "\nCourse lecturer_id: " . $any_course['lecturer_id'];
            echo "\nYour user_id: " . $user_id;
        } else {
            echo "\n\nCourse doesn't exist at all!";
        }
        exit;
    }

    echo "\n\nCourse found: " . $course['name'];
    echo "\nReady to create session!";

} catch (Exception $e) {
    echo "\n\nError: " . $e->getMessage();
}
?>