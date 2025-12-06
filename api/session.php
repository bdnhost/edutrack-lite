<?php
// EduTrack Lite - Session API (התחלת שיעור)
require_once 'config.php';

if (!isLoggedIn()) {
    errorResponse('נא להתחבר למערכת', 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'start':
        startSession();
        break;
    case 'get':
        getSession();
        break;
    case 'close':
        closeSession();
        break;
    case 'attendance':
        getSessionAttendance();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * התחלת שיעור חדש
 */
function startSession()
{
    global $conn;
    $user_id = getUserId();
    $course_id = $_POST['course_id'] ?? 0;
    $lesson_id = $_POST['lesson_id'] ?? null; // NEW: תמיכה במפגש

    if (!$course_id) {
        errorResponse('חסר מזהה קורס');
    }

    // וודא שהקורס שייך למשתמש
    $stmt = $conn->prepare("SELECT id, name FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();

    if (!$course) {
        errorResponse('קורס לא נמצא', 404);
    }

    // אם יש lesson_id, בדוק שהוא שייך לקורס
    if ($lesson_id) {
        $stmt = $conn->prepare("SELECT id, title FROM lessons WHERE id = ? AND course_id = ?");
        $stmt->bind_param("ii", $lesson_id, $course_id);
        $stmt->execute();
        $lesson = $stmt->get_result()->fetch_assoc();

        if (!$lesson) {
            errorResponse('מפגש לא נמצא', 404);
        }
    }

    // בדוק אם יש שיעור פעיל (לכל הקורס או למפגש הזה)
    if ($lesson_id) {
        $stmt = $conn->prepare("SELECT id FROM attendance_sessions WHERE lesson_id = ? AND status = 'active'");
        $stmt->bind_param("i", $lesson_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM attendance_sessions WHERE course_id = ? AND lesson_id IS NULL AND status = 'active'");
        $stmt->bind_param("i", $course_id);
    }
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        // החזר את השיעור הקיים
        $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
        $stmt->bind_param("i", $existing['id']);
        $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc();

        successResponse($session, 'שיעור כבר פעיל');
        return;
    }

    // צור שיעור חדש
    $token = generateToken();
    $expires_at = calculateExpiration(30); // 30 דקות

    if ($lesson_id) {
        $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, lesson_id, token, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $course_id, $lesson_id, $token, $expires_at);
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $token, $expires_at);
    }

    if ($stmt->execute()) {
        $session_id = $conn->insert_id;

        $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc();

        successResponse($session, 'השיעור התחיל');
    } else {
        errorResponse('שגיאה בהתחלת השיעור');
    }
}

/**
 * קבלת פרטי שיעור
 */
function getSession()
{
    global $conn;
    $user_id = getUserId();
    $session_id = $_GET['id'] ?? 0;

    if (!$session_id) {
        errorResponse('חסר מזהה שיעור');
    }

    $sql = "SELECT s.*, c.name as course_name, c.code as course_code
            FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.id
            WHERE s.id = ? AND c.lecturer_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        errorResponse('שיעור לא נמצא', 404);
    }

    $session = $result->fetch_assoc();

    // הוסף מספר נוכחים
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM attendance_records WHERE session_id = ?");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc();
    $session['attendance_count'] = $count['count'];

    successResponse($session);
}

/**
 * סגירת שיעור
 */
function closeSession()
{
    global $conn;
    $user_id = getUserId();

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $session_id = $_POST['session_id'] ?? 0;

    if (!$session_id) {
        errorResponse('חסר מזהה שיעור');
    }

    // וודא שהשיעור שייך למשתמש
    $sql = "SELECT s.id FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.id
            WHERE s.id = ? AND c.lecturer_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('שיעור לא נמצא', 404);
    }

    $stmt = $conn->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
    $stmt->bind_param("i", $session_id);

    if ($stmt->execute()) {
        successResponse([], 'השיעור נסגר');
    } else {
        errorResponse('שגיאה בסגירת השיעור');
    }
}

/**
 * רשימת נוכחות בשיעור
 */
function getSessionAttendance()
{
    global $conn;
    $user_id = getUserId();
    $session_id = $_GET['session_id'] ?? 0;

    if (!$session_id) {
        errorResponse('חסר מזהה שיעור');
    }

    // וודא שהשיעור שייך למשתמש
    $sql = "SELECT s.id FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.id
            WHERE s.id = ? AND c.lecturer_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('שיעור לא נמצא', 404);
    }

    // קבל רשימת נוכחות
    $sql = "SELECT ar.*, s.first_name, s.last_name, s.phone
            FROM attendance_records ar
            INNER JOIN students s ON ar.student_id = s.id
            WHERE ar.session_id = ?
            ORDER BY ar.timestamp DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    successResponse($records);
}

