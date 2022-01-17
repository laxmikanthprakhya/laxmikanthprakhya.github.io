<?php

// Upgrade to the new Patchstack plugin.
update_option('webarx_db_version', '2.2.0');

include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$upgrader = new Plugin_Upgrader( new W_Skin() );
$upgrader->install('https://update.webarxsecurity.com/wp-update-server-clone/?action=download&slug=patchstack', array(
    'overwrite_package' => true
));
activate_plugin('patchstack/patchstack.php');
