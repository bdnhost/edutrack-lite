<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>התקנת EduTrack Lite</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem 0;
    }
    .install-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    .check-item {
      padding: 1rem;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .check-item:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="install-card">
        <div class="card-body p-5">
          <div class="text-center mb-4">
            <i class="bi bi-qr-code-scan" style="font-size: 4rem; color: #667eea;"></i>
            <h1 class="mt-3">EduTrack Lite</h1>
            <p class="text-muted">בדיקת דרישות התקנה</p>
          </div>

          <?php
          $errors = [];
          $warnings = [];
          
          // Check PHP Version
          $php_version = phpversion();
          $php_ok = version_compare($php_version, '7.4.0', '>=');
          
          echo '<div class="check-item">';
          echo '<div><strong>גרסת PHP</strong><br><small class="text-muted">נדרש PHP 7.4 ומעלה</small></div>';
          echo '<div>';
          if ($php_ok) {
            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> ' . $php_version . '</span>';
          } else {
            echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> ' . $php_version . '</span>';
            $errors[] = 'גרסת PHP נמוכה מדי';
          }
          echo '</div></div>';
          
          // Check MySQL Extension
          $mysqli_ok = extension_loaded('mysqli');
          
          echo '<div class="check-item">';
          echo '<div><strong>הרחבת MySQLi</strong><br><small class="text-muted">נדרש לחיבור למסד נתונים</small></div>';
          echo '<div>';
          if ($mysqli_ok) {
            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> מותקן</span>';
          } else {
            echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> לא מותקן</span>';
            $errors[] = 'הרחבת MySQLi לא מותקנת';
          }
          echo '</div></div>';
          
          // Check JSON Extension
          $json_ok = extension_loaded('json');
          
          echo '<div class="check-item">';
          echo '<div><strong>הרחבת JSON</strong><br><small class="text-muted">נדרש לעבודה עם API</small></div>';
          echo '<div>';
          if ($json_ok) {
            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> מותקן</span>';
          } else {
            echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> לא מותקן</span>';
            $errors[] = 'הרחבת JSON לא מותקנת';
          }
          echo '</div></div>';
          
          // Check config.php exists
          $config_exists = file_exists('api/config.php');
          
          echo '<div class="check-item">';
          echo '<div><strong>קובץ הגדרות</strong><br><small class="text-muted">api/config.php</small></div>';
          echo '<div>';
          if ($config_exists) {
            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> קיים</span>';
          } else {
            echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> לא נמצא</span>';
            $errors[] = 'קובץ config.php לא נמצא';
          }
          echo '</div></div>';
          
          // Check uploads directory writable
          $uploads_dir = 'uploads';
          $uploads_writable = is_dir($uploads_dir) && is_writable($uploads_dir);
          
          echo '<div class="check-item">';
          echo '<div><strong>תיקיית העלאות</strong><br><small class="text-muted">הרשאות כתיבה</small></div>';
          echo '<div>';
          if ($uploads_writable) {
            echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> ניתן לכתיבה</span>';
          } else {
            echo '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i> אין הרשאות</span>';
            $warnings[] = 'אין הרשאות כתיבה לתיקיית uploads';
          }
          echo '</div></div>';
          
          // Check database connection
          if ($config_exists) {
            require_once 'api/config.php';
            
            echo '<div class="check-item">';
            echo '<div><strong>חיבור למסד נתונים</strong><br><small class="text-muted">MySQL Connection</small></div>';
            echo '<div>';
            
            if ($conn && $conn->connect_error === null) {
              echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> מחובר</span>';
              
              // Check if tables exist
              $result = $conn->query("SHOW TABLES LIKE 'users'");
              if ($result && $result->num_rows > 0) {
                echo '<div class="mt-2"><small class="text-success">טבלאות מסד הנתונים קיימות ✓</small></div>';
              } else {
                echo '<div class="mt-2"><small class="text-warning">טבלאות לא נמצאו. יש לייבא את schema.sql</small></div>';
                $warnings[] = 'טבלאות מסד הנתונים לא נמצאו';
              }
            } else {
              echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> שגיאת חיבור</span>';
              $errors[] = 'לא ניתן להתחבר למסד הנתונים. בדוק את הגדרות config.php';
            }
            echo '</div></div>';
          }
          
          // Summary
          echo '<div class="mt-4 p-4 rounded">';
          
          if (empty($errors)) {
            echo '<div class="alert alert-success mb-0">';
            echo '<h5><i class="bi bi-check-circle me-2"></i>כל הדרישות מולאו!</h5>';
            echo '<p class="mb-0">המערכת מוכנה לשימוש.</p>';
            echo '<hr>';
            echo '<a href="login.html" class="btn btn-success">התחל להשתמש במערכת <i class="bi bi-arrow-left ms-2"></i></a>';
            echo '</div>';
            
            if (!empty($warnings)) {
              echo '<div class="alert alert-warning mt-3 mb-0">';
              echo '<h6><i class="bi bi-exclamation-triangle me-2"></i>אזהרות:</h6>';
              echo '<ul class="mb-0">';
              foreach ($warnings as $warning) {
                echo '<li>' . $warning . '</li>';
              }
              echo '</ul>';
              echo '</div>';
            }
          } else {
            echo '<div class="alert alert-danger mb-0">';
            echo '<h5><i class="bi bi-x-circle me-2"></i>נמצאו בעיות</h5>';
            echo '<p>יש לתקן את הבעיות הבאות לפני השימוש במערכת:</p>';
            echo '<ul class="mb-0">';
            foreach ($errors as $error) {
              echo '<li>' . $error . '</li>';
            }
            echo '</ul>';
            echo '</div>';
          }
          
          echo '</div>';
          
          echo '<div class="mt-4 text-center">';
          echo '<p class="text-muted mb-2">צריך עזרה? קרא את <a href="README.md" target="_blank">מדריך ההתקנה</a></p>';
          echo '<button class="btn btn-outline-primary" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-2"></i>בדוק שוב</button>';
          echo '</div>';
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
