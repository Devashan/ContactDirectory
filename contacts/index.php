<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); 
$database = DB::create_instance();


// Populate contacts table
$tbl_content = "";

$condition = " WHERE contact_id > 0";
$order_by = " ORDER BY surname ASC, name ASC";
$sql_client_read = "SELECT contact_id, `name`, surname, email FROM Contacts" . $condition . $order_by;

$result = $database->query($sql_client_read);
if ($database->has_results($result)) {
    $tbl_content = '
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                <th class="text-start">Name</th>
                <th class="text-start">Surname</th>
                <th class="text-start">Email</th>
                <th class="text-center">No. of Linked clients</th>
                </tr>
            </thead>
            <tbody>
    ';
    $users = $database->fetchAll($result);
    foreach ($users as $user) {
        $condition = " WHERE contact_id = " . $user['contact_id'];
        $condition .= " AND client_id IS NOT NULL";
        $condition .= " AND `status` = 1";
        $order_by = "";
        
        $sql_client_read = "SELECT COUNT(*) as client_count FROM Client2Contact" . $condition . $order_by;
        $result = $database->query($sql_client_read);
        $client_count = $database->fetchOne($result)['client_count'];
        $tbl_content .= "<tr>";
        $tbl_content .= "   <td><a href='/contacts/edit?id=" . encrypt_data($user['contact_id']) . "'>" . $user['name'] . "</a></td>";
        $tbl_content .= "   <td>" . $user['surname'] . "</td>";
        $tbl_content .= "   <td><a href='mailto:" . $user['email'] . "'>" . $user['email'] . "</a></td>";
        $tbl_content .= "   <td class='text-center'>" . $client_count . "</td>";
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Contacts</h3>
                <a href="/contacts/edit" class="btn btn-primary">New Contact</a>
            </div>

            {$tbl_content}
        </div>
        $bottom_scripts
    </body>
    </html>
HTML;
