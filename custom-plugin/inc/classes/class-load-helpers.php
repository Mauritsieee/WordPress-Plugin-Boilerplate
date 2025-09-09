<?php

namespace CustomPlugin;

// ===================================
// Loading in all helper files,
//   located inside the helper folder
// ===================================

class Helpers {

    // =========================================
    // Enter filesnames from the helpers folder
    // =========================================
    private $filenames = array(
        // 'file.php'
    );

    private function load_helper_files() {
        foreach ( $this->filenames as $file ) {
            require_once CUSTOMPLUGIN . 'inc/helpers/' . $file;
        }
    }

    public function __construct() {
        $this->load_helper_files();
    }
}
