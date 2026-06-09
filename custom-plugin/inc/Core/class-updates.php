<?php

namespace CustomPlugin;

class Updates {

    private static string $endpoint_url = '';
    private static string $domain = '';
    private static string $token = '';
    private static string $plugin_name = '';

    public static function init(): void {
        self::$endpoint_url = CUSTOMPLUGIN_UPDATE_ENDPOINT;
        self::$plugin_name  = self::resolve_plugin_name();
        self::$domain       = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
        self::$token        = CUSTOMPLUGIN_UPDATE_TOKEN;

        self::debug('init', [
            'endpoint_url' => self::$endpoint_url,
            'domain' => self::$domain,
            'plugin_name' => self::$plugin_name,
            'token_present' => self::$token !== '',
            'installed_version' => (string) CUSTOMPLUGIN_VERSION,
        ]);

        add_filter('pre_set_site_transient_update_plugins', [self::class, 'inject_update']);
        add_filter('plugins_api', [self::class, 'plugin_info'], 10, 3);
    }

    public static function fetch_manifest(string $url): ?array {
        self::debug('fetch_manifest.request', [
            'url' => $url,
            'payload' => [
                'domain' => self::$domain,
                'plugin_name' => self::$plugin_name,
                'token_present' => self::$token !== '',
            ],
        ]);

        $response = wp_remote_post($url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => [
                'domain' => self::$domain,
                'token' => self::$token,
                'plugin_name' => self::$plugin_name,
            ],
        ]);

        if (is_wp_error($response)) {
            self::debug('fetch_manifest.error', [
                'error' => $response->get_error_message(),
            ]);
            return null;
        }

        $http_code = (int) wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            self::debug('fetch_manifest.http_code', ['code' => $http_code]);
            return null;
        }

        $body = (string) wp_remote_retrieve_body($response);
        self::debug('fetch_manifest.response', [
            'http_code' => $http_code,
            'body_preview' => substr($body, 0, 300),
        ]);

        $decoded = json_decode($body, true);

        if (!is_array($decoded) || (int) ($decoded['status'] ?? 0) !== 200) {
            self::debug('fetch_manifest.invalid_status', [
                'status' => is_array($decoded) ? (int) ($decoded['status'] ?? 0) : null,
            ]);
            return null;
        }

        if (!isset($decoded['latest'], $decoded['versions']) || !is_array($decoded['versions'])) {
            self::debug('fetch_manifest.invalid_shape', [
                'has_latest' => isset($decoded['latest']),
                'has_versions' => isset($decoded['versions']) && is_array($decoded['versions']),
            ]);
            return null;
        }

        self::debug('fetch_manifest.success', [
            'latest' => (string) $decoded['latest'],
            'versions_count' => count($decoded['versions']),
        ]);

        return $decoded;
    }

    public static function inject_update($transient) {
        if (!is_object($transient) || empty($transient->checked)) {
            self::debug('inject_update.skip', ['reason' => 'invalid_transient']);
            return $transient;
        }

        if (self::$endpoint_url === '') {
            self::debug('inject_update.skip', ['reason' => 'missing_endpoint_url']);
            return $transient;
        }

        $manifest = self::fetch_manifest(self::$endpoint_url);
        if ($manifest === null) {
            self::debug('inject_update.skip', ['reason' => 'manifest_not_available']);
            return $transient;
        }

        $latest = (string) ($manifest['latest'] ?? '');
        if ($latest === '' || !version_compare($latest, (string) CUSTOMPLUGIN_VERSION, '>')) {
            self::debug('inject_update.no_update', [
                'latest' => $latest,
                'installed' => (string) CUSTOMPLUGIN_VERSION,
            ]);
            return $transient;
        }

        $package = (string) ($manifest['versions'][$latest]['download_url'] ?? '');
        if ($package === '') {
            self::debug('inject_update.skip', ['reason' => 'missing_download_url', 'latest' => $latest]);
            return $transient;
        }

        $plugin_file = self::plugin_file();

        $transient->response[$plugin_file] = (object) [
            'slug' => self::$plugin_name,
            'plugin' => $plugin_file,
            'new_version' => $latest,
            'package' => $package,
            'url' => '',
        ];

        self::debug('inject_update.success', [
            'plugin_file' => $plugin_file,
            'new_version' => $latest,
        ]);

        return $transient;
    }

    public static function plugin_info($result, string $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $requested_slug = isset($args->slug) ? (string) $args->slug : '';
        if ($requested_slug !== self::$plugin_name) {
            return $result;
        }

        $manifest = self::fetch_manifest(self::$endpoint_url);
        $latest = is_array($manifest) ? (string) ($manifest['latest'] ?? '') : '';

        if ($latest === '') {
            $latest = (string) CUSTOMPLUGIN_VERSION;
        }

        $info = self::get_info($manifest);
        $sections = self::get_sections($info, $latest);

        return (object) [
            'name' => self::get_info_string($info, 'name', 'Custom WordPress Plugin Boilerplate test'),
            'slug' => self::$plugin_name,
            'version' => $latest,
            'author' => self::get_info_string($info, 'author', '<a href="https://fairfurnituregroup.com/">FFG</a>'),
            'homepage' => self::get_info_string($info, 'homepage', 'https://fairfurnituregroup.com/'),
            'requires' => self::get_info_string($info, 'requires', '6.0'),
            'tested' => self::get_info_string($info, 'tested', get_bloginfo('version')),
            'requires_php' => self::get_info_string($info, 'requires_php', '8.0'),
            'download_link' => self::get_download_link($manifest, $latest),
            'sections' => $sections,
            'banners' => [],
        ];
    }

    private static function resolve_plugin_name(): string {
        $slug = basename(rtrim((string) CUSTOMPLUGIN, '/\\'));
        return $slug !== '' ? $slug : 'plugin';
    }

    private static function plugin_file(): string {
        return plugin_basename(CUSTOMPLUGIN . self::$plugin_name . '.php');
    }

    private static function get_download_link(?array $manifest, string $latest): string {
        if (!is_array($manifest)) {
            return '';
        }

        return (string) ($manifest['versions'][$latest]['download_url'] ?? '');
    }

    private static function get_info(?array $manifest): array {
        if (!is_array($manifest) || !isset($manifest['info']) || !is_array($manifest['info'])) {
            return [];
        }

        return $manifest['info'];
    }

    private static function get_info_string(array $info, string $key, string $default): string {
        if (!isset($info[$key]) || !is_string($info[$key]) || trim($info[$key]) === '') {
            return $default;
        }

        return $info[$key];
    }

    private static function get_sections(array $info, string $latest): array {
        $default = [
            'description' => 'Boilerplate for WordPress Plugins',
            'installation' => 'Upload the plugin folder to wp-content/plugins and activate it.',
            'changelog' => '<ul><li>Version ' . esc_html($latest) . '</li></ul>',
        ];

        if (!isset($info['sections']) || !is_array($info['sections'])) {
            return $default;
        }

        return [
            'description' => self::get_info_string($info['sections'], 'description', $default['description']),
            'installation' => self::get_info_string($info['sections'], 'installation', $default['installation']),
            'changelog' => self::get_info_string($info['sections'], 'changelog', $default['changelog']),
        ];
    }

    private static function debug(string $step, array $context = []): void {
        $key = CUSTOMPLUGIN_PREFIX . 'update_debug';
        $logs = get_option($key, []);

        if (!is_array($logs)) {
            $logs = [];
        }

        $logs[] = [
            'time' => current_time('mysql'),
            'step' => $step,
            'context' => $context,
        ];

        if (count($logs) > 30) {
            $logs = array_slice($logs, -30);
        }

        update_option($key, $logs, false);
    }
}