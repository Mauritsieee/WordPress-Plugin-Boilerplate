<?php

/**
 * This class is fully responsible for loading in all scripts
 * Remove full script and loader if not needed
 */

class CustomPluginScriptLoader {

    private static function load_admin_scripts() {
        
        // define directory
        $directory = plugin_dir_url(__FILE__) . '../admin/';
        
        // enqueue CSS
        wp_enqueue_style('custom-script-css', $directory . 'css/styling.css');
        
        // enqueue JavaScript
        wp_enqueue_script('custom-script-js', $directory . 'js/script.js');
    }
    
    private static function load_frontend_scripts() {
        
        // define directory
        $directory = plugin_dir_url(__FILE__) . '../public/';

        // enqueue CSS
        wp_enqueue_style('custom-script-css', $directory . 'css/styling.css');

        // enqueue JavaScript
        wp_enqueue_script('custom-script-js', $directory . 'js/script.js');
        
    }

    public static function loadScripts() {

        if (is_admin()) {

            self::load_admin_scripts();
        } else {
            
            self::load_frontend_scripts();
        }
    }

}
