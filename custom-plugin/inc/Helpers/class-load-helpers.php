<?php

namespace CustomPlugin;

class Helpers {

    private const FILES = [
        // 'file.php',
    ];

    public static function init(): void {
        foreach (self::FILES as $file) {
            require_once CUSTOMPLUGIN . 'inc/Helpers/' . $file;
        }
    }
}
