<?php

/**
 *
 * Plugin Name:       Custom WordPress Plugin Boilerplate
 * Description:       Boilerplate for WordPress Plugins
 * Version:           1.0.0
 * Author:            MB Development
 * Author URI:        https://mb-dev.net/
 * 
 */

// ====================================
//  If file is called directly, abort.
// ====================================
if ( ! defined( 'WPINC' ) ) die;


define('CUSTOMPLUGIN', plugin_dir_path(__FILE__));
define('CUSTOMPLUGIN_URL', plugin_dir_url(__FILE__));

// ==============================
// Require the main plugin class
// ==============================
require_once CUSTOMPLUGIN . 'inc/classes/class-starter.php';

use CustomPlugin\Starter;

function run_custom_plugin() {
    $custom_plugin = new Starter();
    $custom_plugin->run();
}

run_custom_plugin();

// ==============================
// 
// find-replace the following:
// 
// - custom_plugin
// - CustomPlugin
// - custom-plugin
// - CUSTOMPLUGIN
// 
// ==============================