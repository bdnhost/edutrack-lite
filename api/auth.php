<?php
// EduTrack Lite - Authentication API
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        logout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        errorResponse('פעולה לא חוקית');
}

/**
 * התחברות
 */
function handleLogin()
{
    global $conn;

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        errorResponse('נא למלא את כל השדות');
    }

    $stmt = $conn->prepare("SELECT id, email, password, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        errorResponse('אימייל או סיסמה שגויים');
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        errorResponse('אימייל או סיסמה שגויים');
    }

    // הצלחה - צור session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    successResponse([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name']
        ]
    ], 'התחברת בהצלחה');
}

/**
 * הרשמה
 */
function handleRegister()
{
    global $conn;

    if (!isPost()) {
        errorResponse('שיטה לא חוקית');
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';

    if (empty($email) || empty($password) || empty($full_name)) {
        errorResponse('נא למלא את כל השדות');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        errorResponse('אימייל לא תקין');
    }

    if (strlen($password) < 6) {
        errorResponse('הסיסמה חייבת להכיל לפחות 6 תווים');
    }

    // בדוק אם המשתמש כבר קיים
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        errorResponse('המשתמש כבר קיים במערכת');
    }

    // צור משתמש חדש
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashed_password, $full_name);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;

        // התחבר אוטומטית
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        successResponse([
            'user' => [
                'id' => $user_id,
                'email' => $email,
                'name' => $full_name
            ]
        ], 'נרשמת בהצלחה');
    } else {
        errorResponse('שגיאה ביצירת המשתמש');
    }
}

/**
 * בדיקת אימות
 */
function checkAuth()
{
    global $conn;

    if (isLoggedIn()) {
        $userId = getUserId();

        // שלוף את פרטי המשתמש מהDB
        $stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // שלח תשובה ישירה ללא עטיפת data
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['full_name']
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } else {
            // משתמש לא נמצא - נקה session
            session_destroy();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'authenticated' => false
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    } else {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'authenticated' => false
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

/**
 * התנתקות
 */
function logout()
{
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: ../login.html');
    exit();
}

// ========================================
// IMPORTANT: NO CLOSING PHP TAG!
// This prevents "headers already sent" errors
// ========================================
