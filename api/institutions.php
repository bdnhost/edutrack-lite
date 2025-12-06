<?php
// EduTrack Lite - Institutions API
// ניהול מוסדות לימוד

require_once 'config.php';

if (!isLoggedIn()) {
    errorResponse('נא להתחבר למערכת', 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listInstitutions();
        break;
    case 'create':
        createInstitution();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * רשימת כל המוסדות
 */
function listInstitutions() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM institutions ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $institutions = [];
    while ($row = $result->fetch_assoc()) {
        $institutions[] = $row;
    }
    
    successResponse($institutions);
}

/**
 * הוספת מוסד חדש
 */
function createInstitution() {
    global $conn;
    
    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }
    
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        errorResponse('נא למלא את שם המוסד');
    }
    
    // בדוק כפילויות
    $stmt = $conn->prepare("SELECT id FROM institutions WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        errorResponse('המוסד כבר קיים');
    }
    
    // הוסף מוסד
    $stmt = $conn->prepare("INSERT INTO institutions (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $institution_id = $conn->insert_id;
        successResponse(['id' => $institution_id], 'המוסד נוסף בהצלחה');
    } else {
        errorResponse('שגיאה בהוספת המוסד');
    }
}
