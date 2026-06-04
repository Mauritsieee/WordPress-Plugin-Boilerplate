<?php

namespace CustomPlugin;

class Dependencies {

	private const REQUIRED_PLUGINS = [
		// 'plugin/plugin.php'   => 'Plugin name',
	];

	public static function init(): void {
		if (!is_admin()) {
			return;
		}

		add_action('admin_notices', [self::class, 'render_missing_plugins_notice']);
	}

	public static function render_missing_plugins_notice(): void {
		if (!current_user_can('activate_plugins')) {
			return;
		}

		$missing = self::get_missing_plugins();

		if (empty($missing)) {
			return;
		}

		$plugins = implode(', ', $missing);
		$current_plugin_name = self::get_current_plugin_name();

		echo '<div class="notice notice-error"><p>';
		echo esc_html($current_plugin_name . ' requires the following plugin(s) to be installed: ' . $plugins . '.');
		echo '</p></div>';
	}

	private static function get_current_plugin_name(): string {
		$plugin_file = CUSTOMPLUGIN . 'custom-plugin.php';

		if (function_exists('get_file_data')) {
			$plugin_data = get_file_data($plugin_file, ['Name' => 'Plugin Name']);

			if (!empty($plugin_data['Name'])) {
				return (string) $plugin_data['Name'];
			}
		}

		return 'This plugin';
	}

	private static function get_missing_plugins(): array {
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		$missing = [];

		foreach (self::REQUIRED_PLUGINS as $plugin_file => $plugin_name) {
			if (!isset($installed_plugins[$plugin_file])) {
				$missing[] = $plugin_name;
			}
		}

		return $missing;
	}
}
