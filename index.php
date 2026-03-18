<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = DB::create_instance();


// Populate clients table
$tbl_content = "";

$condition = " WHERE client_id > 0";
$order_by = " ORDER BY name ASC";
$sql_client_read = "SELECT * FROM Clients" . $condition . $order_by;

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
        $condition = " WHERE client_id = " . $user['client_id'];
        $condition .= " AND contact_id IS NOT NULL";
        $condition .= " AND `status` = 1";
        $order_by = "";
        
        $sql_client_read = "SELECT COUNT(*) as contact_count FROM Client2Contact" . $condition . $order_by;
        $result = $database->query($sql_client_read);
        $contact_count = $database->fetchOne($result)['contact_count'];
        $tbl_content .= "<tr>";
        $tbl_content .= "   <td><a href='/clients/edit?id=" . encrypt_data($user['client_id']) . "'>" . $user['name'] . "</a></td>";
        $tbl_content .= "   <td>" . $user['client_code'] . "</td>";
        $tbl_content .= "   <td class='text-center'>" . $contact_count . "</td>";
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

$database->close();

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
