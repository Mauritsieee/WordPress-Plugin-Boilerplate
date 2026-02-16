<?php

namespace CustomPlugin;

// ================================================================
// Plugin bootstrap – loads and initializes all core functionality
// ================================================================

require_once CUSTOMPLUGIN . 'inc/Helpers/class-load-helpers.php';
require_once CUSTOMPLUGIN . 'inc/Core/class-script-loader.php';
require_once CUSTOMPLUGIN . 'inc/Core/class-db.php';

// ================================================================
// Load plugin features
// ================================================================

require_once CUSTOMPLUGIN . 'inc/Features/custom-feature.php';


class Starter {

    public static function init(): void {

        // ==================
        // Core initializers
        // ==================

        Helpers::init();
        ScriptLoader::init();

        // ==================
        // Database check
        // ==================

        Database::maybe_upgrade();

        // ==================
        // Initialize features
        // ==================

        \CustomPlugin\Features\Feature::init();
    }

    public static function activate(): void {
        Database::create_table();
    }
}
