<?php

namespace CustomPlugin;

// ===========================================================
// This class is fully responsible for loading in all scripts
//   and styling for both front- and backend
// ===========================================================

class CustomPluginScriptLoader {
    
    public static function loadScripts() {
        if (is_admin()) {
            self::load_admin_scripts();
        } else {
            self::load_frontend_scripts();
        }
    }

    private static function load_admin_scripts() {
       
        // ============================
        // Backend script and styling
        // ============================

        wp_enqueue_style(
            'custom-plugin-script-css',
            CUSTOMPLUGIN_URL . 'admin/css/styling.css'
        );

        wp_enqueue_script(
            'custom-plugin-script-js',
            CUSTOMPLUGIN_URL . 'admin/js/script.js',
            ['jquery'],
            null,
            true
        );
    }

    private static function load_frontend_scripts() {
        
        // ============================
        // Frontend script and styling
        // ============================

        wp_enqueue_style(
            'custom-plugin-script-css', 
            CUSTOMPLUGIN_URL . 'public/css/styling.css'
        );

        wp_enqueue_script(
            'custom-plugin-script-js',
            CUSTOMPLUGIN_URL . 'public/js/script.js',
            ['jquery'],
            null,
            true
        );
    }
}
