<?php
// EduTrack Lite - Students API
require_once 'config.php';

if (!isLoggedIn()) {
    errorResponse('נא להתחבר למערכת', 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listStudents();
        break;
    case 'get':
        getStudent();
        break;
    case 'create':
        createStudent();
        break;
    case 'import':
        importStudents();
        break;
    case 'delete':
        deleteStudent();
        break;
    case 'stats':
        getStudentStats();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * רשימת תלמידים בקורס
 */
function listStudents()
{
    global $conn;
    $user_id = getUserId();
    $course_id = $_GET['course_id'] ?? 0;

    if (!$course_id) {
        errorResponse('חסר מזהה קורס');
    }

    // וודא שהקורס שייך למשתמש
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }

    $stmt = $conn->prepare("SELECT * FROM students WHERE course_id = ? ORDER BY last_name, first_name");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    successResponse($students);
}

/**
 * הוספת תלמיד בודד
 */
function createStudent()
{
    global $conn;
    $user_id = getUserId();

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $course_id = $_POST['course_id'] ?? 0;
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? null;

    if (!$course_id || empty($first_name) || empty($last_name) || empty($phone)) {
        errorResponse('נא למלא את כל השדות הנדרשים');
    }

    // וודא שהקורס שייך למשתמש
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }

    // נרמל טלפון
    $normalized_phone = normalizePhone($phone);
    if (!$normalized_phone) {
        errorResponse('מספר טלפון לא תקין');
    }

    // בדוק כפילויות
    $stmt = $conn->prepare("SELECT id FROM students WHERE course_id = ? AND phone = ?");
    $stmt->bind_param("is", $course_id, $normalized_phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        errorResponse('התלמיד כבר קיים בקורס');
    }

    // הוסף תלמיד
    $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, phone, email, course_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $first_name, $last_name, $normalized_phone, $email, $course_id);

    if ($stmt->execute()) {
        $student_id = $conn->insert_id;
        successResponse(['id' => $student_id], 'התלמיד נוסף בהצלחה');
    } else {
        errorResponse('שגיאה בהוספת התלמיד');
    }
}

/**
 * ייבוא תלמידים מ-CSV
 */
function importStudents()
{
    global $conn;
    $user_id = getUserId();

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $course_id = $_POST['course_id'] ?? 0;

    if (!$course_id) {
        errorResponse('חסר מזהה קורס');
    }

    // וודא שהקורס שייך למשתמש
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }

    // בדוק אם יש קובץ
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        errorResponse('לא הועלה קובץ');
    }

    $file = $_FILES['file']['tmp_name'];
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

    // תמיכה רק ב-CSV
    if ($ext !== 'csv') {
        errorResponse('יש לייבא קובץ CSV בלבד');
    }

    $students = [];
    $errors = [];
    $success_count = 0;
    $line_number = 0;

    if (($handle = fopen($file, 'r')) !== FALSE) {
        // דלג על שורת כותרת
        $header = fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $line_number++;

            // נסה למצוא את העמודות הנכונות
            $first_name = trim($data[0] ?? '');
            $last_name = trim($data[1] ?? '');
            $phone = trim($data[2] ?? '');
            $email = trim($data[3] ?? '');

            // ולידציה
            if (empty($first_name) || empty($last_name) || empty($phone)) {
                $errors[] = "שורה $line_number: חסרים שדות נדרשים";
                continue;
            }

            $normalized_phone = normalizePhone($phone);
            if (!$normalized_phone) {
                $errors[] = "שורה $line_number: מספר טלפון לא תקין - $phone";
                continue;
            }

            // בדוק כפילויות
            $stmt = $conn->prepare("SELECT id FROM students WHERE course_id = ? AND phone = ?");
            $stmt->bind_param("is", $course_id, $normalized_phone);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "שורה $line_number: $first_name $last_name כבר קיים";
                continue;
            }

            // הוסף תלמיד
            $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, phone, email, course_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $first_name, $last_name, $normalized_phone, $email, $course_id);

            if ($stmt->execute()) {
                $success_count++;
            } else {
                $errors[] = "שורה $line_number: שגיאה בהוספת $first_name $last_name";
            }
        }
        fclose($handle);
    } else {
        errorResponse('שגיאה בקריאת הקובץ');
    }

    successResponse([
        'success_count' => $success_count,
        'error_count' => count($errors),
        'errors' => $errors
    ], "$success_count תלמידים יובאו בהצלחה");
}

/**
 * מחיקת תלמיד
 */
function deleteStudent()
{
    global $conn;
    $user_id = getUserId();
    $student_id = $_GET['id'] ?? 0;

    if (!$student_id) {
        errorResponse('חסר מזהה תלמיד');
    }

    // וודא שהתלמיד שייך לקורס של המשתמש
    $stmt = $conn->prepare("
        SELECT s.id FROM students s
        INNER JOIN courses c ON s.course_id = c.id
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->bind_param("ii", $student_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('תלמיד לא נמצא', 404);
    }

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        successResponse([], 'התלמיד נמחק בהצלחה');
    } else {
        errorResponse('שגיאה במחיקת התלמיד');
    }
}

/**
 * סטטיסטיקות נוכחות של תלמיד
 */
function getStudentStats()
{
    global $conn;
    $user_id = getUserId();
    $student_id = $_GET['id'] ?? 0;

    if (!$student_id) {
        errorResponse('חסר מזהה תלמיד');
    }

    // וודא שהתלמיד שייך לקורס של המשתמש
    $stmt = $conn->prepare("
        SELECT s.id FROM students s
        INNER JOIN courses c ON s.course_id = c.id
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->bind_param("ii", $student_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('תלמיד לא נמצא', 404);
    }

    $stmt = $conn->prepare("SELECT * FROM student_attendance_stats WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        successResponse($result->fetch_assoc());
    } else {
        successResponse([
            'total_attended' => 0,
            'total_sessions' => 0,
            'attendance_percentage' => 0
        ]);
    }
}

