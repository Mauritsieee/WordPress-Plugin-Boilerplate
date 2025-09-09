<?php

// ================================================
// If uninstall was not called by WordPress, exit.
// ================================================

// ================================================
// 
// ================================================

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


namespace CustomPlugin;

class Uninstall_CustomPlugin {

    public static function removeDatabase() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_plugin_table';

        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query( $sql );

    }

}

Uninstall_CustomPlugin::removeDatabase();