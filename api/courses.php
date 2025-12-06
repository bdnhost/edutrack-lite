<?php
// EduTrack Lite - Courses API
require_once 'config.php';

if (!isLoggedIn()) {
    errorResponse('נא להתחבר למערכת', 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listCourses();
        break;
    case 'get':
        getCourse();
        break;
    case 'create':
        createCourse();
        break;
    case 'update':
        updateCourse();
        break;
    case 'delete':
        deleteCourse();
        break;
    case 'stats':
        getCourseStats();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * רשימת כל הקורסים של המשתמש
 */
function listCourses()
{
    global $conn;
    $user_id = getUserId();

    $sql = "SELECT c.*, i.name as institution_name,
            (SELECT COUNT(*) FROM students WHERE course_id = c.id) as student_count,
            (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = c.id AND status = 'closed') as session_count
            FROM courses c
            LEFT JOIN institutions i ON c.institution_id = i.id
            WHERE c.lecturer_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    successResponse($courses);
}

/**
 * פרטי קורס בודד
 */
function getCourse()
{
    global $conn;
    $user_id = getUserId();
    $course_id = $_GET['id'] ?? 0;

    if (!$course_id) {
        errorResponse('חסר מזהה קורס');
    }

    $sql = "SELECT c.*, i.name as institution_name,
            (SELECT COUNT(*) FROM students WHERE course_id = c.id) as student_count,
            (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = c.id AND status = 'closed') as session_count
            FROM courses c
            LEFT JOIN institutions i ON c.institution_id = i.id
            WHERE c.id = ? AND c.lecturer_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }

    successResponse($result->fetch_assoc());
}

/**
 * יצירת קורס חדש
 */
function createCourse()
{
    global $conn;
    $user_id = getUserId();

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? null;
    $institution_id = $_POST['institution_id'] ?? 0;
    $semester = $_POST['semester'] ?? '';

    if (empty($name) || empty($semester) || !$institution_id) {
        errorResponse('נא למלא את כל השדות הנדרשים');
    }

    $stmt = $conn->prepare("INSERT INTO courses (name, code, institution_id, semester, lecturer_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisi", $name, $code, $institution_id, $semester, $user_id);

    if ($stmt->execute()) {
        $course_id = $conn->insert_id;
        successResponse(['id' => $course_id], 'הקורס נוצר בהצלחה');
    } else {
        errorResponse('שגיאה ביצירת הקורס');
    }
}

/**
 * עדכון קורס
 */
function updateCourse()
{
    global $conn;
    $user_id = getUserId();

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $course_id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? null;
    $institution_id = $_POST['institution_id'] ?? 0;
    $semester = $_POST['semester'] ?? '';

    if (!$course_id || empty($name) || empty($semester) || !$institution_id) {
        errorResponse('נא למלא את כל השדות הנדרשים');
    }

    $stmt = $conn->prepare("UPDATE courses SET name = ?, code = ?, institution_id = ?, semester = ? WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ssissi", $name, $code, $institution_id, $semester, $course_id, $user_id);

    if ($stmt->execute()) {
        successResponse([], 'הקורס עודכן בהצלחה');
    } else {
        errorResponse('שגיאה בעדכון הקורס');
    }
}

/**
 * מחיקת קורס (העברה לארכיון)
 */
function deleteCourse()
{
    global $conn;
    $user_id = getUserId();
    $course_id = $_GET['id'] ?? 0;

    if (!$course_id) {
        errorResponse('חסר מזהה קורס');
    }

    $stmt = $conn->prepare("UPDATE courses SET status = 'archived' WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);

    if ($stmt->execute()) {
        successResponse([], 'הקורס הועבר לארכיון');
    } else {
        errorResponse('שגיאה במחיקת הקורס');
    }
}

/**
 * סטטיסטיקות קורס
 */
function getCourseStats()
{
    global $conn;
    $user_id = getUserId();
    $course_id = $_GET['id'] ?? 0;

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

    // קבל סטטיסטיקות מה-VIEW
    $stmt = $conn->prepare("SELECT * FROM course_attendance_stats WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        successResponse($result->fetch_assoc());
    } else {
        successResponse([
            'total_students' => 0,
            'total_sessions' => 0,
            'total_attendance_records' => 0,
            'average_attendance_percentage' => 0
        ]);
    }
}
