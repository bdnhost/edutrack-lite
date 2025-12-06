<?php
/**
 * Define the internationalization functionality.
 */
class Edutrack_i18n
{
    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'edutrack-lite',
            false,
            dirname(EDUTRACK_PLUGIN_BASENAME) . '/languages/'
        );
    }
}
