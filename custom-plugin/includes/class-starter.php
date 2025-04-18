<?php

// load in required files
require_once plugin_dir_path(__FILE__) . 'class-render-scripts.php';
require_once plugin_dir_path(__FILE__) . 'class-db-handler.php';

class CustomPlugin {

    public $custom_plugin_db;

    public function run() {
        // run plugin here
    }

    public function __construct() {

        // load in scripts
        CustomPluginScriptLoader::loadScripts();

        $custom_plugin_db = new CustomPlugin_DB_Handler();
        
    }
}