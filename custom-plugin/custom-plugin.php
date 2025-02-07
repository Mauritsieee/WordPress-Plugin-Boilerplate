<?php

/**
 *
 * Plugin Name:       Custom Plugin Boilerplate
 * Description:       Boilerplate for WordPress Plugins
 * Version:           1.0.0
 * Author:            MB Development
 * Author URI:        https://mb-dev.net/
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Require the main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-custom-plugin.php';

function run_custom_plugin() {
    $custom_plugin = new CustomPlugin();
    $custom_plugin->run();
}

run_custom_plugin();
