<?php

namespace CustomPlugin;

class Database {

    private const TABLE_SUFFIX = 'custom_plugin_table';
    private const OPTION_KEY   = CUSTOMPLUGIN_PREFIX . 'db_version';
    private const DB_VERSION   = '1.0.0';

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    public static function create_table(): void {

        global $wpdb;

        $table_name      = self::table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            column_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::OPTION_KEY, self::DB_VERSION);
    }

    public static function maybe_upgrade(): void {

        if (get_option(self::OPTION_KEY) !== self::DB_VERSION) {
            self::create_table();
        }
    }
}
