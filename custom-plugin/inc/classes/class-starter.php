<?php

namespace CustomPlugin;

// ================================================================
// Starter of the full plugin, 
//   loading and starting all the functionality within this plugin
// ================================================================

require_once CUSTOMPLUGIN . 'inc/classes/class-load-helpers.php';
require_once CUSTOMPLUGIN . 'inc/classes/class-render-scripts.php';
require_once CUSTOMPLUGIN . 'inc/classes/class-db-handler.php';

class Starter {

    public $custom_plugin_db;

    public function run() {

        // ================
        // run plugin here
        // ================

    }

    public function __construct() {

        // ===================================================================
        // Loading helper and script class.
        //  saving database functionality within the public $custom_plugin_db
        // ===================================================================

        new \CustomPlugin\Helpers();

        \CustomPlugin\CustomPluginScriptLoader::loadScripts();

        $this->custom_plugin_db = new \CustomPlugin\CustomPlugin_DB_Handler();

    }
}
