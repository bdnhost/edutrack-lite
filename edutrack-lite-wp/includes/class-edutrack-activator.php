<?php
/**
 * Fired during plugin activation and deactivation.
 */
class Edutrack_Activator
{
    /**
     * Activate the plugin.
     *
     * - Create database tables
     * - Add custom roles and capabilities
     * - Set default options
     */
    public static function activate()
    {
        require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-database.php';

        // Create database tables
        Edutrack_Database::create_tables();

        // Run database migrations (for updates)
        Edutrack_Database::run_migrations();

        // Add custom roles
        self::add_custom_roles();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin.
     */
    public static function deactivate()
    {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Note: We don't remove roles or delete data on deactivation
        // Users might just be temporarily disabling the plugin
    }

    /**
     * Add custom user roles and capabilities.
     */
    private static function add_custom_roles()
    {
        // Add EduTrack Lecturer role
        add_role(
            'edutrack_lecturer',
            __('EduTrack Lecturer', 'edutrack-lite'),
            array(
                'read'                      => true,
                'edit_posts'                => false,
                'delete_posts'              => false,
                'manage_edutrack'           => true,  // Custom capability
                'view_edutrack_dashboard'   => true,
                'manage_edutrack_courses'   => true,
                'manage_edutrack_students'  => true,
                'manage_edutrack_attendance'=> true,
            )
        );

        // Add capabilities to administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_edutrack');
            $admin_role->add_cap('view_edutrack_dashboard');
            $admin_role->add_cap('manage_edutrack_courses');
            $admin_role->add_cap('manage_edutrack_students');
            $admin_role->add_cap('manage_edutrack_attendance');
            $admin_role->add_cap('manage_edutrack_settings');
        }
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options()
    {
        $default_options = array(
            'session_timeout'   => 30,  // minutes
            'date_format'       => 'd/m/Y',
            'time_format'       => 'H:i',
            'institutions'      => array(
                'מכללת עתיד',
                'מכללת מערב הגליל',
                'מכללת ORT',
                'אחר'
            ),
        );

        // Only set if not already exists
        if (!get_option('edutrack_settings')) {
            add_option('edutrack_settings', $default_options);
        }

        // Set version
        add_option('edutrack_version', EDUTRACK_VERSION);
    }

    /**
     * Uninstall the plugin.
     * Called when plugin is deleted.
     */
    public static function uninstall()
    {
        // Remove custom roles
        remove_role('edutrack_lecturer');

        // Remove capabilities from administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('manage_edutrack');
            $admin_role->remove_cap('view_edutrack_dashboard');
            $admin_role->remove_cap('manage_edutrack_courses');
            $admin_role->remove_cap('manage_edutrack_students');
            $admin_role->remove_cap('manage_edutrack_attendance');
            $admin_role->remove_cap('manage_edutrack_settings');
        }

        // Only delete data if user confirms
        if (get_option('edutrack_delete_on_uninstall', false)) {
            Edutrack_Database::drop_tables();
            delete_option('edutrack_settings');
            delete_option('edutrack_version');
            delete_option('edutrack_delete_on_uninstall');
        }
    }
}
