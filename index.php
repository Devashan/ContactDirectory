<?php
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>$system_title</title>
    $top_scripts
</head>
<body>
    $navbar
    <div class="container mt-4">
    <div class="d-flex justify-content-center align-items-center mb-3">
        <a href="/clients/" style="text-decoration: none;">
            <div class="card w-auto">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="fa-solid fa-building" style="font-size: 5rem;"></i>
                    <div class="mt-2"></div>
                    <h5 class="card-title">View Clients</h5>
                </div>
            </div>
        </a>
        <div class="mx-3"></div>
        <a href="/contacts/" style="text-decoration: none;">
            <div class="card w-auto">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="fa-solid fa-users" style="font-size: 5rem;"></i>
                    <div class="mt-2"></div>
                    <h5 class="card-title">View Contacts</h5>
                </div>
            </div>
        </a>
        </div>
    $bottom_scripts
</body>
</html>
HTML;
