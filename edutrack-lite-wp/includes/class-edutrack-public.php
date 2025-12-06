<?php
/**
 * The public-facing functionality of the plugin.
 */
class Edutrack_Public
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side.
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            EDUTRACK_PLUGIN_URL . 'public/css/edutrack-public.css',
            array(),
            $this->version,
            'all'
        );

        // Bootstrap 5 RTL
        wp_enqueue_style(
            'bootstrap-rtl',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css',
            array(),
            '5.3.0'
        );

        // Bootstrap Icons
        wp_enqueue_style(
            'bootstrap-icons',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
            array(),
            '1.11.0'
        );
    }

    /**
     * Register the JavaScript for the public-facing side.
     */
    public function enqueue_scripts()
    {
        // Bootstrap 5
        wp_enqueue_script(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.0',
            true
        );

        // QRCode.js
        wp_enqueue_script(
            'qrcodejs',
            'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js',
            array(),
            '1.0.0',
            true
        );

        // Main public script
        wp_enqueue_script(
            $this->plugin_name,
            EDUTRACK_PLUGIN_URL . 'public/js/edutrack-public.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script
        wp_localize_script(
            $this->plugin_name,
            'edutrackPublic',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('edutrack_public_nonce'),
                'siteUrl' => home_url(),
            )
        );
    }

    /**
     * Register all shortcodes.
     */
    public function register_shortcodes()
    {
        add_shortcode('edutrack_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('edutrack_course', array($this, 'course_shortcode'));
        add_shortcode('edutrack_attend', array($this, 'attend_shortcode'));
        add_shortcode('edutrack_session', array($this, 'session_shortcode'));
    }

    /**
     * Dashboard shortcode.
     * Usage: [edutrack_dashboard]
     */
    public function dashboard_shortcode($atts)
    {
        // Check if user is logged in and has permission
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">יש להתחבר כדי לצפות בדשבורד.</div>';
        }

        if (!current_user_can('manage_edutrack')) {
            return '<div class="alert alert-danger">אין לך הרשאות לצפות בדשבורד.</div>';
        }

        ob_start();
        include EDUTRACK_PLUGIN_DIR . 'public/templates/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Course shortcode.
     * Usage: [edutrack_course id="123"]
     */
    public function course_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">יש להתחבר כדי לצפות בקורס.</div>';
        }

        if (!current_user_can('manage_edutrack')) {
            return '<div class="alert alert-danger">אין לך הרשאות לצפות בקורס.</div>';
        }

        $course_id = intval($atts['id']);
        if ($course_id <= 0) {
            return '<div class="alert alert-danger">מזהה קורס לא תקין.</div>';
        }

        ob_start();
        include EDUTRACK_PLUGIN_DIR . 'public/templates/course.php';
        return ob_get_clean();
    }

    /**
     * Attend shortcode - for students to mark attendance.
     * Usage: [edutrack_attend token="abc123"]
     */
    public function attend_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'token' => '',
        ), $atts);

        // Get token from URL if not in shortcode
        $token = !empty($atts['token']) ? $atts['token'] : (isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '');

        if (empty($token)) {
            return '<div class="alert alert-danger">קוד נוכחות לא תקין.</div>';
        }

        ob_start();
        include EDUTRACK_PLUGIN_DIR . 'public/templates/attend.php';
        return ob_get_clean();
    }

    /**
     * Session shortcode - displays active session with QR code.
     * Usage: [edutrack_session id="123"]
     */
    public function session_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">יש להתחבר כדי לצפת בשיעור.</div>';
        }

        if (!current_user_can('manage_edutrack')) {
            return '<div class="alert alert-danger">אין לך הרשאות לצפות בשיעור.</div>';
        }

        $session_id = intval($atts['id']);
        if ($session_id <= 0) {
            return '<div class="alert alert-danger">מזהה שיעור לא תקין.</div>';
        }

        ob_start();
        include EDUTRACK_PLUGIN_DIR . 'public/templates/session.php';
        return ob_get_clean();
    }
}
