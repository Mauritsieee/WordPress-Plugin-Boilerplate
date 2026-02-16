<?php

namespace CustomPlugin\Features;

class Feature {

    public static function init(): void {
        add_action('init', [self::class, 'success']);
    }

    public static function success(): void {
        printf(
            'The WordPress Plugin Boilerplate version %s is installed.',
            esc_html(CUSTOMPLUGIN_VERSION)
        );
    }
}
