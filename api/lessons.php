<?php
// EduTrack Lite - Lessons API
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    errorResponse('נדרש אימות', 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listLessons();
        break;
    case 'create':
        createLesson();
        break;
    case 'update':
        updateLesson();
        break;
    case 'delete':
        deleteLesson();
        break;
    case 'import':
        importLessons();
        break;
    case 'stats':
        getLessonStats();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * רשימת מפגשים לקורס
 */
function listLessons() {
    global $conn;
    
    $course_id = $_GET['course_id'] ?? 0;
    
    if (!$course_id) {
        errorResponse('נדרש מזהה קורס');
    }
    
    // בדוק שהקורס שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }
    
    // שלוף מפגשים
    $stmt = $conn->prepare("
        SELECT 
            l.*,
            COUNT(DISTINCT asess.id) as sessions_count,
            COUNT(DISTINCT ar.id) as total_attendances,
            (SELECT COUNT(*) FROM students WHERE course_id = l.course_id) as total_students
        FROM lessons l
        LEFT JOIN attendance_sessions asess ON asess.lesson_id = l.id
        LEFT JOIN attendance_records ar ON ar.session_id = asess.id
        WHERE l.course_id = ?
        GROUP BY l.id
        ORDER BY l.lesson_number ASC
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lessons = [];
    while ($row = $result->fetch_assoc()) {
        $lessons[] = $row;
    }
    
    successResponse($lessons);
}

/**
 * יצירת מפגש חדש
 */
function createLesson() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $course_id = $_POST['course_id'] ?? 0;
    $lesson_number = $_POST['lesson_number'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $planned_date = $_POST['planned_date'] ?? null;
    $planned_time = $_POST['planned_time'] ?? null;
    
    if (!$course_id || !$lesson_number || !$title) {
        errorResponse('נא למלא את כל השדות הנדרשים');
    }
    
    // בדוק שהקורס שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }
    
    // בדוק שמספר המפגש לא קיים
    $stmt = $conn->prepare("SELECT id FROM lessons WHERE course_id = ? AND lesson_number = ?");
    $stmt->bind_param("ii", $course_id, $lesson_number);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        errorResponse('מפגש עם מספר זה כבר קיים');
    }
    
    // צור מפגש
    $stmt = $conn->prepare("
        INSERT INTO lessons (course_id, lesson_number, title, description, planned_date, planned_time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissss", $course_id, $lesson_number, $title, $description, $planned_date, $planned_time);
    
    if ($stmt->execute()) {
        $lesson_id = $conn->insert_id;
        
        // שלוף את המפגש שנוצר
        $stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        $lesson = $stmt->get_result()->fetch_assoc();
        
        successResponse($lesson, 'מפגש נוצר בהצלחה');
    } else {
        errorResponse('שגיאה ביצירת המפגש');
    }
}

/**
 * עדכון מפגש
 */
function updateLesson() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $lesson_id = $_POST['lesson_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $planned_date = $_POST['planned_date'] ?? null;
    $planned_time = $_POST['planned_time'] ?? null;
    
    if (!$lesson_id || !$title) {
        errorResponse('נא למלא את כל השדות הנדרשים');
    }
    
    // בדוק שהמפגש שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("
        SELECT l.id FROM lessons l
        JOIN courses c ON c.id = l.course_id
        WHERE l.id = ? AND c.lecturer_id = ?
    ");
    $stmt->bind_param("ii", $lesson_id, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('מפגש לא נמצא', 404);
    }
    
    // עדכן מפגש
    $stmt = $conn->prepare("
        UPDATE lessons 
        SET title = ?, description = ?, planned_date = ?, planned_time = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $title, $description, $planned_date, $planned_time, $lesson_id);
    
    if ($stmt->execute()) {
        successResponse([], 'מפגש עודכן בהצלחה');
    } else {
        errorResponse('שגיאה בעדכון המפגש');
    }
}

/**
 * מחיקת מפגש
 */
function deleteLesson() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $lesson_id = $_POST['lesson_id'] ?? 0;
    
    if (!$lesson_id) {
        errorResponse('נדרש מזהה מפגש');
    }
    
    // בדוק שהמפגש שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("
        SELECT l.id FROM lessons l
        JOIN courses c ON c.id = l.course_id
        WHERE l.id = ? AND c.lecturer_id = ?
    ");
    $stmt->bind_param("ii", $lesson_id, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('מפגש לא נמצא', 404);
    }
    
    // מחק מפגש
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    
    if ($stmt->execute()) {
        successResponse([], 'מפגש נמחק בהצלחה');
    } else {
        errorResponse('שגיאה במחיקת המפגש');
    }
}

/**
 * ייבוא מפגשים מ-CSV
 */
function importLessons() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $course_id = $_POST['course_id'] ?? 0;
    
    if (!$course_id) {
        errorResponse('נדרש מזהה קורס');
    }
    
    // בדוק שהקורס שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $course_id, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        errorResponse('קורס לא נמצא', 404);
    }
    
    // בדוק קובץ
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        errorResponse('לא הועלה קובץ');
    }
    
    $file = $_FILES['file']['tmp_name'];
    
    // קרא CSV
    $handle = fopen($file, 'r');
    if (!$handle) {
        errorResponse('שגיאה בקריאת הקובץ');
    }
    
    // דלג על שורת כותרות
    fgetcsv($handle);
    
    $success_count = 0;
    $errors = [];
    $line = 2;
    
    while (($data = fgetcsv($handle)) !== false) {
        $lesson_number = trim($data[0] ?? '');
        $title = trim($data[1] ?? '');
        $description = trim($data[2] ?? '');
        $planned_date = trim($data[3] ?? '');
        $planned_time = trim($data[4] ?? '');
        
        // וולידציה
        if (empty($lesson_number) || empty($title)) {
            $errors[] = "שורה $line: חסרים שדות חובה";
            $line++;
            continue;
        }
        
        // בדוק אם קיים
        $stmt = $conn->prepare("SELECT id FROM lessons WHERE course_id = ? AND lesson_number = ?");
        $stmt->bind_param("ii", $course_id, $lesson_number);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "שורה $line: מפגש מספר $lesson_number כבר קיים";
            $line++;
            continue;
        }
        
        // הכנס
        $stmt = $conn->prepare("
            INSERT INTO lessons (course_id, lesson_number, title, description, planned_date, planned_time)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $planned_date_val = !empty($planned_date) ? $planned_date : null;
        $planned_time_val = !empty($planned_time) ? $planned_time : null;
        
        $stmt->bind_param("iissss", $course_id, $lesson_number, $title, $description, $planned_date_val, $planned_time_val);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $errors[] = "שורה $line: " . $stmt->error;
        }
        
        $line++;
    }
    
    fclose($handle);
    
    successResponse([
        'imported' => $success_count,
        'errors' => $errors
    ], "$success_count מפגשים יובאו בהצלחה");
}

/**
 * סטטיסטיקות מפגש
 */
function getLessonStats() {
    global $conn;
    
    $lesson_id = $_GET['lesson_id'] ?? 0;
    
    if (!$lesson_id) {
        errorResponse('נדרש מזהה מפגש');
    }
    
    // בדוק שהמפגש שייך למשתמש
    $userId = getUserId();
    $stmt = $conn->prepare("
        SELECT l.* FROM lessons l
        JOIN courses c ON c.id = l.course_id
        WHERE l.id = ? AND c.lecturer_id = ?
    ");
    $stmt->bind_param("ii", $lesson_id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        errorResponse('מפגש לא נמצא', 404);
    }
    
    $lesson = $result->fetch_assoc();
    
    // סטטיסטיקות
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT asess.id) as sessions_count,
            COUNT(DISTINCT ar.id) as total_attendances,
            COUNT(DISTINCT ar.student_id) as unique_students,
            (SELECT COUNT(*) FROM students WHERE course_id = ?) as total_students
        FROM lessons l
        LEFT JOIN attendance_sessions asess ON asess.lesson_id = l.id
        LEFT JOIN attendance_records ar ON ar.session_id = asess.id
        WHERE l.id = ?
    ");
    $stmt->bind_param("ii", $lesson['course_id'], $lesson_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
    successResponse([
        'lesson' => $lesson,
        'stats' => $stats
    ]);
}
