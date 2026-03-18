<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = DB::create_instance();

$contact_id_enc = $_GET['id'] ?? null;
$client_id_enc = $_GET['client_id'] ?? null;

if (!empty($client_id_enc) and !empty($contact_id_enc)) {
  $client_id = decrypt_data($client_id_enc);
  $contact_id = decrypt_data($contact_id_enc);

  if ($client_id > 0 and $contact_id > 0) {
    $values = "updated_at = '$now'";
    $values .= ", status = 0";

    $sql = "UPDATE Client2Contact SET $values WHERE client_id = $client_id AND contact_id = $contact_id";
    $database->query($sql);
    if ($database->affected_rows() > 0) {
      $feedback = '
        <div class="alert alert-success">
          Client unlinked successfully!
        </div>
      ';
    } else {
      $feedback = '
        <div class="alert alert-danger">
          Failed to unlink client.
        </div>
      ';
    }
  }
} else {
  $feedback = '
    <div class="alert alert-danger">
      Invalid client or contact ID.
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
      <h3>Unlink Client</h3>
      {$feedback}
      <meta http-equiv="refresh" content="2;url=/contacts/edit/?id={$contact_id_enc}">
    </div>
    $bottom_scripts
  </body>
</html>
HTML;
