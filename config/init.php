<?php

require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/database.php';


// Create Layout Variables
$header_data = require_once __DIR__ . '/../layouts/header.php';
extract($header_data); 

$footer_data = require_once __DIR__ . '/../layouts/footer.php';
extract($footer_data); 

return [
    'system_title' => $system_title,
    'top_scripts' => $top_scripts,
    'bottom_scripts' => $bottom_scripts,
    'db' => DB::create_instance()
];

  