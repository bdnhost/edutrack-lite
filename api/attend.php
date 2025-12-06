<?php
// EduTrack Lite - Attendance API (רישום נוכחות לתלמיד)
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check':
        checkSession();
        break;
    case 'register':
        registerAttendance();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * בדיקת תקינות טוקן (לפני רישום)
 */
function checkSession() {
    global $conn;
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        errorResponse('טוקן חסר');
    }
    
    $sql = "SELECT s.*, c.name as course_name, c.code as course_code, i.name as institution_name
            FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.id
            INNER JOIN institutions i ON c.institution_id = i.id
            WHERE s.token = ? AND s.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        errorResponse('שיעור לא נמצא או הסתיים', 404);
    }
    
    $session = $result->fetch_assoc();
    
    // בדוק אם פג תוקף
    if (strtotime($session['expires_at']) < time()) {
        errorResponse('חלון הנוכחות נסגר');
    }
    
    successResponse([
        'session_id' => $session['id'],
        'course_name' => $session['course_name'],
        'course_code' => $session['course_code'],
        'institution_name' => $session['institution_name'],
        'expires_at' => $session['expires_at']
    ]);
}

/**
 * רישום נוכחות
 */
function registerAttendance() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $token = $_POST['token'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (empty($token) || empty($phone)) {
        errorResponse('נא למלא את כל השדות');
    }
    
    // נרמל טלפון
    $normalized_phone = normalizePhone($phone);
    if (!$normalized_phone) {
        errorResponse('מספר טלפון לא תקין. נדרש 10 ספרות המתחיל ב-0');
    }
    
    // מצא את השיעור
    $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE token = ? AND status = 'active'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        errorResponse('שיעור לא נמצא או הסתיים');
    }
    
    // בדוק תוקף
    if (strtotime($session['expires_at']) < time()) {
        errorResponse('חלון הנוכחות נסגר');
    }
    
    // מצא תלמיד לפי טלפון וקורס
    $stmt = $conn->prepare("SELECT * FROM students WHERE phone = ? AND course_id = ?");
    $stmt->bind_param("si", $normalized_phone, $session['course_id']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if (!$student) {
        errorResponse('מספר טלפון לא נמצא במערכת. אנא פנה למרצה.');
    }
    
    // בדוק אם כבר נרשם
    $stmt = $conn->prepare("SELECT id FROM attendance_records WHERE session_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $session['id'], $student['id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        errorResponse('כבר נרשמת לשיעור זה');
    }
    
    // רשום נוכחות
    $stmt = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, phone_entered) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $session['id'], $student['id'], $normalized_phone);
    
    if ($stmt->execute()) {
        successResponse([
            'student_name' => $student['first_name'] . ' ' . $student['last_name'],
            'timestamp' => date('Y-m-d H:i:s')
        ], 'נוכחות נרשמה בהצלחה! ✓');
    } else {
        errorResponse('שגיאה ברישום נוכחות');
    }
}
