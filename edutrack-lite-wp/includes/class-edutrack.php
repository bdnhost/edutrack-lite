<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Edutrack
{
    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct()
    {
        $this->version = EDUTRACK_VERSION;
        $this->plugin_name = 'edutrack-lite';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_ajax_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies()
    {
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-loader.php';
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-i18n.php';
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-database.php';
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-admin.php';
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-public.php';
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-ajax.php';

        $this->loader = new Edutrack_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale()
    {
        $plugin_i18n = new Edutrack_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Edutrack_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks()
    {
        $plugin_public = new Edutrack_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
    }

    /**
     * Register all AJAX hooks.
     */
    private function define_ajax_hooks()
    {
        $plugin_ajax = new Edutrack_Ajax();

        // For logged-in users
        $this->loader->add_action('wp_ajax_edutrack_get_courses', $plugin_ajax, 'get_courses');
        $this->loader->add_action('wp_ajax_edutrack_create_course', $plugin_ajax, 'create_course');
        $this->loader->add_action('wp_ajax_edutrack_get_course', $plugin_ajax, 'get_course');
        $this->loader->add_action('wp_ajax_edutrack_update_course', $plugin_ajax, 'update_course');
        $this->loader->add_action('wp_ajax_edutrack_delete_course', $plugin_ajax, 'delete_course');
        $this->loader->add_action('wp_ajax_edutrack_get_students', $plugin_ajax, 'get_students');
        $this->loader->add_action('wp_ajax_edutrack_add_student', $plugin_ajax, 'add_student');
        $this->loader->add_action('wp_ajax_edutrack_import_students', $plugin_ajax, 'import_students');
        $this->loader->add_action('wp_ajax_edutrack_update_student', $plugin_ajax, 'update_student');
        $this->loader->add_action('wp_ajax_edutrack_delete_student', $plugin_ajax, 'delete_student');
        $this->loader->add_action('wp_ajax_edutrack_get_lessons', $plugin_ajax, 'get_lessons');
        $this->loader->add_action('wp_ajax_edutrack_add_lesson', $plugin_ajax, 'add_lesson');
        $this->loader->add_action('wp_ajax_edutrack_update_lesson', $plugin_ajax, 'update_lesson');
        $this->loader->add_action('wp_ajax_edutrack_delete_lesson', $plugin_ajax, 'delete_lesson');
        $this->loader->add_action('wp_ajax_edutrack_start_session', $plugin_ajax, 'start_session');
        $this->loader->add_action('wp_ajax_edutrack_close_session', $plugin_ajax, 'close_session');
        $this->loader->add_action('wp_ajax_edutrack_get_session_status', $plugin_ajax, 'get_session_status');
        $this->loader->add_action('wp_ajax_edutrack_get_attendance', $plugin_ajax, 'get_attendance');
        $this->loader->add_action('wp_ajax_edutrack_get_institutions', $plugin_ajax, 'get_institutions');
        $this->loader->add_action('wp_ajax_edutrack_get_active_session', $plugin_ajax, 'get_active_session');
        $this->loader->add_action('wp_ajax_edutrack_get_course_sessions', $plugin_ajax, 'get_course_sessions');
        $this->loader->add_action('wp_ajax_edutrack_export_students', $plugin_ajax, 'export_students');
        $this->loader->add_action('wp_ajax_edutrack_export_session_attendance', $plugin_ajax, 'export_session_attendance');

        // For non-logged-in users (students marking attendance)
        $this->loader->add_action('wp_ajax_nopriv_edutrack_mark_attendance', $plugin_ajax, 'mark_attendance');
        $this->loader->add_action('wp_ajax_nopriv_edutrack_verify_session', $plugin_ajax, 'verify_session');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
