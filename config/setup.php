<?php
date_default_timezone_set('Africa/Johannesburg');

// Load environment variables
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    putenv("$key=$value");
}

$now_full = new DateTime();
$now = $now_full->format('Y-m-d H:i:s');