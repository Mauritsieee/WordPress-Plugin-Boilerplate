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

		echo '<div class="notice notice-error"><p>';
		echo esc_html('GF Survey Export Add-on requires the following plugin(s) to be installed: ' . $plugins . '.');
		echo '</p></div>';
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
