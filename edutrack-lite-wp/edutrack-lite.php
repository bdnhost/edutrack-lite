<?php
/**
 * Plugin Name: EduTrack Lite
 * Plugin URI: https://github.com/bdnhost/edutrack-lite
 * Description: מערכת ניהול נוכחות חכמה עם QR Code לוורדפרס - ניהול קורסים, תלמידים ומעקב נוכחות בזמן אמת
 * Version: 1.0.0
 * Author: יעקב
 * Author URI: https://github.com/bdnhost
 * Text Domain: edutrack-lite
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin version
 */
define('EDUTRACK_VERSION', '1.0.0');
define('EDUTRACK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDUTRACK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDUTRACK_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Session timeout in minutes (for attendance QR codes)
 */
define('EDUTRACK_SESSION_TIMEOUT', 30);

/**
 * The code that runs during plugin activation.
 */
function activate_edutrack()
{
    require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-activator.php';
    Edutrack_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_edutrack()
{
    require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack-activator.php';
    Edutrack_Activator::deactivate();
}

register_activation_hook(__FILE__, 'activate_edutrack');
register_deactivation_hook(__FILE__, 'deactivate_edutrack');

/**
 * The core plugin class
 */
require_once EDUTRACK_PLUGIN_DIR . 'includes/class-edutrack.php';

/**
 * Begins execution of the plugin.
 */
function run_edutrack()
{
    $plugin = new Edutrack();
    $plugin->run();
}

run_edutrack();
