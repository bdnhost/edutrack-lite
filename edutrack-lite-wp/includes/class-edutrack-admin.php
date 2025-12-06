<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Edutrack_Admin
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles()
    {
        if (!$this->is_edutrack_page()) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name,
            EDUTRACK_PLUGIN_URL . 'admin/css/edutrack-admin.css',
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
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts()
    {
        if (!$this->is_edutrack_page()) {
            return;
        }

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

        // Main admin script
        wp_enqueue_script(
            $this->plugin_name,
            EDUTRACK_PLUGIN_URL . 'admin/js/edutrack-admin.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script(
            $this->plugin_name,
            'edutrackAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('edutrack_nonce'),
                'siteUrl' => home_url(),
            )
        );
    }

    /**
     * Register the administration menu for this plugin.
     */
    public function add_plugin_admin_menu()
    {
        // Check if user has capability
        if (!current_user_can('manage_edutrack')) {
            return;
        }

        // Main menu
        add_menu_page(
            __('EduTrack Lite', 'edutrack-lite'),
            __('EduTrack', 'edutrack-lite'),
            'manage_edutrack',
            'edutrack',
            array($this, 'display_dashboard_page'),
            'dashicons-welcome-learn-more',
            30
        );

        // Dashboard submenu (same as main)
        add_submenu_page(
            'edutrack',
            __('Dashboard', 'edutrack-lite'),
            __('Dashboard', 'edutrack-lite'),
            'manage_edutrack',
            'edutrack',
            array($this, 'display_dashboard_page')
        );

        // Courses submenu
        add_submenu_page(
            'edutrack',
            __('Courses', 'edutrack-lite'),
            __('Courses', 'edutrack-lite'),
            'manage_edutrack_courses',
            'edutrack-courses',
            array($this, 'display_courses_page')
        );

        // Settings submenu
        add_submenu_page(
            'edutrack',
            __('Settings', 'edutrack-lite'),
            __('Settings', 'edutrack-lite'),
            'manage_edutrack_settings',
            'edutrack-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Render the dashboard page.
     */
    public function display_dashboard_page()
    {
        require_once EDUTRACK_PLUGIN_DIR . 'admin/pages/dashboard.php';
    }

    /**
     * Render the courses page.
     */
    public function display_courses_page()
    {
        require_once EDUTRACK_PLUGIN_DIR . 'admin/pages/courses.php';
    }

    /**
     * Render the settings page.
     */
    public function display_settings_page()
    {
        require_once EDUTRACK_PLUGIN_DIR . 'admin/pages/settings.php';
    }

    /**
     * Register plugin settings.
     */
    public function register_settings()
    {
        register_setting(
            'edutrack_settings_group',
            'edutrack_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'edutrack_general_settings',
            __('General Settings', 'edutrack-lite'),
            array($this, 'settings_section_callback'),
            'edutrack-settings'
        );

        add_settings_field(
            'session_timeout',
            __('Session Timeout (minutes)', 'edutrack-lite'),
            array($this, 'session_timeout_callback'),
            'edutrack-settings',
            'edutrack_general_settings'
        );
    }

    /**
     * Settings section callback.
     */
    public function settings_section_callback()
    {
        echo '<p>' . __('Configure EduTrack Lite settings.', 'edutrack-lite') . '</p>';
    }

    /**
     * Session timeout field callback.
     */
    public function session_timeout_callback()
    {
        $options = get_option('edutrack_settings');
        $timeout = isset($options['session_timeout']) ? $options['session_timeout'] : 30;
        echo '<input type="number" name="edutrack_settings[session_timeout]" value="' . esc_attr($timeout) . '" min="5" max="120" />';
        echo '<p class="description">' . __('How long should QR code sessions remain active (5-120 minutes).', 'edutrack-lite') . '</p>';
    }

    /**
     * Sanitize settings.
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        if (isset($input['session_timeout'])) {
            $timeout = intval($input['session_timeout']);
            $sanitized['session_timeout'] = max(5, min(120, $timeout));
        }

        return $sanitized;
    }

    /**
     * Check if current page is an EduTrack page.
     */
    private function is_edutrack_page()
    {
        $screen = get_current_screen();
        return $screen && strpos($screen->id, 'edutrack') !== false;
    }
}
