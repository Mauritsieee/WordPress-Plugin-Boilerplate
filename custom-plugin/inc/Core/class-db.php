<?php

namespace CustomPlugin;

class Database {

    private const OPTION_KEY   = CUSTOMPLUGIN_PREFIX . 'db_version';
    private const DB_VERSION   = '1.0.0';

    public static function create_tables(): void {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Copy this whole block to add another table.
        self::create_table_block(
            'custom_plugin_table',
            "
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            column_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ",
            $charset_collate
        );

        update_option(self::OPTION_KEY, self::DB_VERSION);
    }

    private static function table(string $tablename): string {
        global $wpdb;
        return $wpdb->prefix . CUSTOMPLUGIN_PREFIX . $tablename;
    }
    
    private static function run_dbdelta(string $sql): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    private static function create_table_block(string $tablename, string $columns_sql, string $charset_collate): void {
        $table_name = self::table($tablename);
        $sql = "CREATE TABLE {$table_name} ({$columns_sql}) {$charset_collate};";
        self::run_dbdelta($sql);
    }
    
    public static function maybe_upgrade(): void {

        if (get_option(self::OPTION_KEY) !== self::DB_VERSION) {
            self::create_tables();
        }
    }
}
