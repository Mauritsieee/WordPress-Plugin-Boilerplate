<?php

/**
 *
 * Plugin Name:       Custom WordPress Plugin Boilerplate
 * Description:       Boilerplate for WordPress Plugins
 * Version:           1.0.0
 * Author:            FFG
 * Author URI:        https://fairfurnituregroup.com/
 *
 */

// ====================================
// If file is called directly, abort.
// ====================================
if (!defined('ABSPATH')) {
    die;
}

define('CUSTOMPLUGIN', plugin_dir_path(__FILE__));
define('CUSTOMPLUGIN_URL', plugin_dir_url(__FILE__));

$plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
define('CUSTOMPLUGIN_VERSION', $plugin_data['Version']);

define('CUSTOMPLUGIN_PREFIX', 'customplugin_');


// ==============================
// Load plugin bootstrap
// ==============================
require_once CUSTOMPLUGIN . 'inc/Core/class-starter.php';

// ==============================
// Initialize plugin
// ==============================
\CustomPlugin\Starter::init();

// ==============================
// Activation hook
// ==============================
register_activation_hook(__FILE__, ['\CustomPlugin\Starter', 'activate']);


/*
|--------------------------------------------------------------------------
| Find & Replace:
|--------------------------------------------------------------------------
| custom_plugin
| CustomPlugin
| custom-plugin
| CUSTOMPLUGIN
|--------------------------------------------------------------------------
*/
