<?php

namespace CustomPlugin;

class Updates {

    private static string $endpoint_url = '';
    private static string $domain = '';
    private static string $token = '';
    private static string $plugin_name = '';
    private static string $request_plugin_name = '';
    private static string $plugin_file = '';
    private static string $installed_version = '0.0.0';
    private static string $plugin_display_name = '';
    private static string $plugin_author = '';
    private static string $plugin_homepage = '';
    private static string $plugin_requires = '';
    private static string $plugin_requires_php = '';
    private static string $plugin_description = '';
    private static bool $initialized = false;

    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        self::resolve_plugin_context();
        self::$endpoint_url = self::resolve_endpoint_url();
        self::$token = self::resolve_token();
        self::$request_plugin_name = self::resolve_request_plugin_name(self::$plugin_name);
        self::$domain = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);

        if (self::$endpoint_url === '') {
            self::debug('init.skip', ['reason' => 'missing_endpoint_url']);
            return;
        }

        if (self::$token === '') {
            self::debug('init.skip', ['reason' => 'missing_update_token']);
            return;
        }

        if (self::$request_plugin_name === '') {
            self::debug('init.skip', ['reason' => 'missing_request_plugin_name']);
            return;
        }

        self::debug('init', [
            'endpoint_url' => self::$endpoint_url,
            'domain' => self::$domain,
            'plugin_name' => self::$plugin_name,
            'request_plugin_name' => self::$request_plugin_name,
            'plugin_file' => self::$plugin_file,
            'token_present' => self::$token !== '',
            'installed_version' => self::$installed_version,
        ]);

        add_filter('pre_set_site_transient_update_plugins', [self::class, 'inject_update']);
        add_filter('plugins_api', [self::class, 'plugin_info'], 10, 3);

        self::$initialized = true;
    }

    public static function fetch_manifest(string $url): ?array {
        $request_plugin_name = self::$request_plugin_name !== ''
            ? self::$request_plugin_name
            : self::resolve_request_plugin_name(self::$plugin_name);

        self::debug('fetch_manifest.request', [
            'url' => $url,
            'payload' => [
                'domain' => self::$domain,
                'plugin_name' => $request_plugin_name,
                'version' => self::$installed_version,
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
                'plugin_name' => $request_plugin_name,
                'version' => self::$installed_version,
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
        if (str_starts_with($body, "\xEF\xBB\xBF")) {
            $body = substr($body, 3);
        }

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
        if ($latest === '' || !version_compare($latest, self::$installed_version, '>')) {
            self::debug('inject_update.no_update', [
                'latest' => $latest,
                'installed' => self::$installed_version,
            ]);

            return $transient;
        }

        $package = (string) ($manifest['versions'][$latest]['download_url'] ?? '');
        if ($package === '') {
            self::debug('inject_update.skip', ['reason' => 'missing_download_url', 'latest' => $latest]);

            return $transient;
        }

        if (self::$plugin_file === '') {
            self::debug('inject_update.skip', ['reason' => 'missing_plugin_file']);

            return $transient;
        }

        $transient->response[self::$plugin_file] = (object) [
            'slug' => self::$plugin_name,
            'plugin' => self::$plugin_file,
            'new_version' => $latest,
            'package' => $package,
            'url' => '',
        ];

        self::debug('inject_update.success', [
            'plugin_file' => self::$plugin_file,
            'new_version' => $latest,
        ]);

        return $transient;
    }

    public static function plugin_info($result, string $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $requested_slug = isset($args->slug) ? (string) $args->slug : '';
        if (sanitize_key($requested_slug) !== sanitize_key(self::$plugin_name)) {
            return $result;
        }

        $manifest = self::fetch_manifest(self::$endpoint_url);
        $latest = is_array($manifest) ? (string) ($manifest['latest'] ?? '') : '';

        if ($latest === '') {
            $latest = self::$installed_version;
        }

        $info = self::get_info($manifest);
        $sections = self::get_sections($info, $latest);

        return (object) [
            'name' => self::get_info_string($info, 'name', self::$plugin_display_name),
            'slug' => self::$plugin_name,
            'version' => $latest,
            'author' => self::get_info_string($info, 'author', self::$plugin_author),
            'homepage' => self::get_info_string($info, 'homepage', self::$plugin_homepage),
            'requires' => self::get_info_string($info, 'requires', self::$plugin_requires),
            'tested' => self::get_info_string($info, 'tested', get_bloginfo('version')),
            'requires_php' => self::get_info_string($info, 'requires_php', self::$plugin_requires_php),
            'download_link' => self::get_download_link($manifest, $latest),
            'sections' => $sections,
            'banners' => [],
        ];
    }

    private static function resolve_plugin_context(): void {
        $main_file = self::main_plugin_file();
        $headers = self::read_plugin_headers($main_file);

        $plugin_file = self::resolve_plugin_file($main_file);
        $plugin_slug = dirname($plugin_file);

        if ($plugin_slug === '.' || $plugin_slug === '') {
            $plugin_slug = basename($main_file, '.php');
        }

        self::$plugin_file = $plugin_file;
        self::$plugin_name = (string) $plugin_slug;
        self::$installed_version = $headers['Version'] !== ''
            ? (string) $headers['Version']
            : self::resolve_installed_version_constant();
        self::$plugin_display_name = (string) $headers['Name'];
        self::$plugin_author = (string) $headers['Author'];
        self::$plugin_homepage = (string) $headers['PluginURI'];
        self::$plugin_requires = (string) $headers['Requires'];
        self::$plugin_requires_php = (string) $headers['RequiresPHP'];
        self::$plugin_description = (string) $headers['Description'];
    }

    private static function resolve_endpoint_url(): string {
        if (defined('CUSTOMPLUGIN_UPDATE_ENDPOINT')) {
            return trim((string) constant('CUSTOMPLUGIN_UPDATE_ENDPOINT'));
        }

        return '';
    }

    private static function resolve_token(): string {
        if (defined('CUSTOMPLUGIN_UPDATE_TOKEN')) {
            return trim((string) constant('CUSTOMPLUGIN_UPDATE_TOKEN'));
        }

        return '';
    }

    private static function resolve_installed_version_constant(): string {
        if (defined('CUSTOMPLUGIN_VERSION')) {
            return (string) constant('CUSTOMPLUGIN_VERSION');
        }

        return '0.0.0';
    }

    private static function resolve_request_plugin_name(string $plugin_name): string {
        $normalized = sanitize_key($plugin_name);

        if (str_ends_with($normalized, '-main')) {
            $normalized = substr($normalized, 0, -5);
        }

        return $normalized;
    }

    private static function main_plugin_file(): string {
        $plugin_dir = rtrim((string) CUSTOMPLUGIN, '/\\');
        $default_slug = basename($plugin_dir);
        $preferred_main = $plugin_dir . '/' . $default_slug . '.php';

        if (is_file($preferred_main) && self::has_plugin_header($preferred_main)) {
            return $preferred_main;
        }

        $candidate_files = glob($plugin_dir . '/*.php') ?: [];

        foreach ($candidate_files as $candidate_file) {
            if (basename($candidate_file) === basename(__FILE__)) {
                continue;
            }

            if (self::has_plugin_header($candidate_file)) {
                return $candidate_file;
            }
        }

        if (is_file($preferred_main)) {
            return $preferred_main;
        }

        return (string) ($candidate_files[0] ?? __FILE__);
    }

    private static function resolve_plugin_file(string $main_file): string {
        if (defined('CUSTOMPLUGIN_BASENAME')) {
            return (string) constant('CUSTOMPLUGIN_BASENAME');
        }

        return plugin_basename($main_file);
    }

    private static function has_plugin_header(string $file): bool {
        $headers = self::read_plugin_headers($file);

        return $headers['Name'] !== '';
    }

    private static function read_plugin_headers(string $file): array {
        if (!is_file($file)) {
            return [
                'Name' => '',
                'Version' => '',
                'Author' => '',
                'PluginURI' => '',
                'Requires' => '',
                'RequiresPHP' => '',
                'Description' => '',
            ];
        }

        $headers = get_file_data($file, [
            'Name' => 'Plugin Name',
            'Version' => 'Version',
            'Author' => 'Author',
            'PluginURI' => 'Plugin URI',
            'Requires' => 'Requires at least',
            'RequiresPHP' => 'Requires PHP',
            'Description' => 'Description',
        ]);

        return [
            'Name' => isset($headers['Name']) ? (string) $headers['Name'] : '',
            'Version' => isset($headers['Version']) ? (string) $headers['Version'] : '',
            'Author' => isset($headers['Author']) ? (string) $headers['Author'] : '',
            'PluginURI' => isset($headers['PluginURI']) ? (string) $headers['PluginURI'] : '',
            'Requires' => isset($headers['Requires']) ? (string) $headers['Requires'] : '',
            'RequiresPHP' => isset($headers['RequiresPHP']) ? (string) $headers['RequiresPHP'] : '',
            'Description' => isset($headers['Description']) ? (string) $headers['Description'] : '',
        ];
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
            'description' => self::$plugin_description,
            'installation' => '',
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
        $key = defined('CUSTOMPLUGIN_PREFIX')
            ? (string) constant('CUSTOMPLUGIN_PREFIX') . 'update_debug'
            : 'customplugin_update_debug';

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
