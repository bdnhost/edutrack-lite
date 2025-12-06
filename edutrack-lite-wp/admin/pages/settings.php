<?php
/**
 * Admin Settings Page
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_edutrack_settings')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Handle form submission
if (isset($_POST['edutrack_settings_submit'])) {
    check_admin_referer('edutrack_settings_nonce');

    $settings = array(
        'session_timeout' => isset($_POST['session_timeout']) ? intval($_POST['session_timeout']) : 30,
    );

    update_option('edutrack_settings', $settings);
    echo '<div class="notice notice-success"><p>ההגדרות נשמרו בהצלחה!</p></div>';
}

$settings = get_option('edutrack_settings', array('session_timeout' => 30));
?>

<div class="wrap edutrack-admin">
    <h1 class="wp-heading-inline">
        <i class="bi bi-gear"></i>
        <?php echo esc_html__('הגדרות EduTrack', 'edutrack-lite'); ?>
    </h1>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">הגדרות כלליות</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <?php wp_nonce_field('edutrack_settings_nonce'); ?>

                        <div class="mb-4">
                            <label for="session_timeout" class="form-label">
                                <strong>זמן תפוגה של שיעור (בדקות)</strong>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="session_timeout"
                                   name="session_timeout"
                                   value="<?php echo esc_attr($settings['session_timeout']); ?>"
                                   min="5"
                                   max="120"
                                   style="max-width: 200px;">
                            <div class="form-text">
                                כמה זמן QR Code יישאר פעיל לאחר פתיחת שיעור (5-120 דקות)
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6><i class="bi bi-info-circle"></i> מידע על המערכת</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>גרסת הפלאגין:</strong></td>
                                    <td><?php echo EDUTRACK_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>גרסת מסד נתונים:</strong></td>
                                    <td><?php echo Edutrack_Database::get_db_version(); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>גרסת PHP:</strong></td>
                                    <td><?php echo PHP_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>גרסת WordPress:</strong></td>
                                    <td><?php echo get_bloginfo('version'); ?></td>
                                </tr>
                            </table>
                        </div>

                        <button type="submit" name="edutrack_settings_submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> שמור הגדרות
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-question-circle"></i> עזרה ותמיכה</h5>
                </div>
                <div class="card-body">
                    <h6>Shortcodes זמינים:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <code>[edutrack_dashboard]</code><br>
                            <small>דשבורד למרצה</small>
                        </li>
                        <li class="mb-2">
                            <code>[edutrack_course id="123"]</code><br>
                            <small>עמוד קורס ספציפי</small>
                        </li>
                        <li class="mb-2">
                            <code>[edutrack_attend]</code><br>
                            <small>דף נוכחות לתלמידים</small>
                        </li>
                    </ul>

                    <hr>

                    <h6>תפקידים ו-capabilities:</h6>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-check-circle text-success"></i> Administrator - גישה מלאה</li>
                        <li><i class="bi bi-check-circle text-success"></i> EduTrack Lecturer - ניהול קורסים</li>
                    </ul>

                    <hr>

                    <p class="mb-0">
                        <a href="https://github.com/bdnhost/edutrack-lite" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-github"></i> GitHub
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
