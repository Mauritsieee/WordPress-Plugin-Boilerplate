<?php

class CustomPlugin_DB_Handler {

    private $wpdb;
    private $table_name;

    public function __construct() {

        global $wpdb;
        $this->wpdb = $wpdb;

        $this->table_name = $this->wpdb->prefix . 'custom_plugin_table';

        $this->create_table();
        
    }

    // check if table exists, extending of create_table
    private function is_table_exists() {
        $sql = "SHOW TABLES LIKE %s";
        $table_exists = $this->wpdb->get_var( $this->wpdb->prepare( $sql, $this->table_name ) );
        return ! empty( $table_exists );
    }

    
    public function create_table() {
        if ( ! $this->is_table_exists() ) {

            $charset_collate = $this->wpdb->get_charset_collate();

            // set database structure here
            $sql = "CREATE TABLE $this->table_name (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                column_name VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }


    /*
    
    add more database-related functions

    */


}
