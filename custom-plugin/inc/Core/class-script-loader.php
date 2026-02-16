<?php

namespace CustomPlugin;

class ScriptLoader {

    public static function init(): void {
        add_action('wp_enqueue_scripts', [self::class, 'frontend']);
        add_action('admin_enqueue_scripts', [self::class, 'admin']);
    }

    // =========================
    // Frontend assets
    // =========================
    public static function frontend(): void {

        self::style('assets/css/public.css');
        self::script('assets/js/public.js', ['jquery']);
    }

    // =========================
    // Admin assets
    // =========================
    public static function admin(): void {

        self::style('assets/css/admin.css');
        self::script('assets/js/admin.js', ['jquery']);
    }

    private static function style(string $path, array $deps = []): void {

        wp_enqueue_style(
            self::handle($path),
            CUSTOMPLUGIN_URL . $path,
            $deps,
            CUSTOMPLUGIN_VERSION
        );
    }

    private static function script(string $path, array $deps = []): void {

        wp_enqueue_script(
            self::handle($path),
            CUSTOMPLUGIN_URL . $path,
            $deps,
            CUSTOMPLUGIN_VERSION,
            true
        );
    }

    private static function handle(string $path): string {
        return CUSTOMPLUGIN_PREFIX . pathinfo($path, PATHINFO_FILENAME);
    }
}
