<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

/**
 * This class is used to activate and deactivate the plugin.
 * Additionally, we use it to run migrations.
 */
class W_Skin extends WP_Upgrader_Skin {
    public function feedback($string, ...$args)
    {

    }
}
