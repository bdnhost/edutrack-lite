<?php
// בדיקת הוספת תלמיד
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in'], JSON_PRETTY_PRINT);
    exit;
}

$user_id = getUserId();
$course_id = $_GET['course_id'] ?? 2;

echo "Testing student creation for course $course_id\n\n";

// בדוק אם הקורס שייך למשתמש
$stmt = $conn->prepare("SELECT id, name FROM courses WHERE id = ? AND lecturer_id = ?");
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "ERROR: Course not found or doesn't belong to user!\n";
    echo "User ID: $user_id\n";
    echo "Course ID: $course_id\n";
    exit;
}

echo "✅ Course found: " . $course['name'] . "\n\n";

// נסה להוסיף תלמיד
$first_name = "תלמיד";
$last_name = "בדיקה";
$phone = "0501234567";
$email = "test@example.com";

echo "Attempting to add student:\n";
echo "Name: $first_name $last_name\n";
echo "Phone: $phone\n";
echo "Email: $email\n\n";

try {
    // נרמל טלפון
    $normalized_phone = preg_replace('/\D/', '', $phone);
    if (strlen($normalized_phone) === 9 && !str_starts_with($normalized_phone, '0')) {
        $normalized_phone = '0' . $normalized_phone;
    }

    echo "Normalized phone: $normalized_phone\n\n";

    // בדוק כפילויות
    $stmt = $conn->prepare("SELECT id FROM students WHERE course_id = ? AND phone = ?");
    $stmt->bind_param("is", $course_id, $normalized_phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "⚠️ Student already exists\n";
    } else {
        // הוסף תלמיד
        $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, phone, email, course_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $first_name, $last_name, $normalized_phone, $email, $course_id);

        if ($stmt->execute()) {
            $student_id = $conn->insert_id;
            echo "✅ Student added successfully!\n";
            echo "Student ID: $student_id\n";
        } else {
            echo "❌ Error: " . $stmt->error . "\n";
        }
    }

    // הצג את כל התלמידים בקורס
    echo "\n\nAll students in course:\n";
    $stmt = $conn->prepare("SELECT * FROM students WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($student = $result->fetch_assoc()) {
        echo "- {$student['first_name']} {$student['last_name']} ({$student['phone']})\n";
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>