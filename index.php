<?php
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = $init['db'];


// Populate clients table
$tbl_content = "";
$sql_client_read = "SELECT * FROM Clients";
$result = $database->query($sql_client_read);
if ($database->has_results($result)) {
    $tbl_content = '
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th class="text-start">Name</th>
                    <th class="text-start">Client Code</th>
                    <th class="text-center">Contacts</th>
                </tr>
            </thead>
            <tbody>
    ';
    $users = $database->fetchAll($result);
    foreach ($users as $user) {
        $tbl_content .= "<tr>";
        $tbl_content .= "   <td>" . $user['name'] . "</td>";
        $tbl_content .= "   <td>" . $user['client_code'] . "</td>";
        $tbl_content .= "   <td class='text-center'>" . $user['contacts'] . "</td>";
        $tbl_content .= "</tr>";
    }
    $tbl_content .= '
            </tbody>
        </table>
    ';
} else {
    $tbl_content = '
        <div class="alert alert-secondary">
            No client(s) found.
        </div>
    ';
}

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
            <!-- PAGE CONTENT GOES HERE -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Clients</h3>
                <a href="/clients/edit" class="btn btn-primary">New Client</a>
            </div>

            {$tbl_content}
        </div>
        $bottom_scripts
    </body>
    </html>
HTML;
