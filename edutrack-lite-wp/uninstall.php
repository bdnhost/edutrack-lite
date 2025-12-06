<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is executed when the user explicitly uninstalls the plugin
 * from the WordPress admin panel.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load the activator class
require_once plugin_dir_path(__FILE__) . 'includes/class-edutrack-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edutrack-database.php';

// Run the uninstall
Edutrack_Activator::uninstall();
