<?php

// ================================================
// If uninstall was not called by WordPress, exit.
// ================================================
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// ================================================
// Remove custom database table
// ================================================

global $wpdb;

$table_name = $wpdb->prefix . 'custom_plugin_table';

$wpdb->query("DROP TABLE IF EXISTS {$table_name}");
delete_option('customplugin_db_version');
