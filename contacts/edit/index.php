<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = DB::create_instance();

$contact_id_enc = $_GET['id'] ?? null;
if (!empty($contact_id_enc)) {
  $contact_id = decrypt_data($contact_id_enc);
  if ($contact_id > 0) {
    $new_contact = false;
    $condition = " WHERE contact_id = " . $contact_id;
    $sql_contact_read = "SELECT * FROM Contacts" . $condition;

    $result = $database->query($sql_contact_read);
    if ($database->has_results($result)) {
      $contact = $database->fetchOne($result);

      $contact_id_enc = encrypt_data($contact['contact_id']);
      $contact_name = $contact['name'];
      $contact_surname = $contact['surname'];
      $contact_email = $contact['email'];
      $contact_code = $contact['contact_code'];
    } else {
      $error = '
      <div class="alert alert-warning">
        No contact(s) found. You are creating a new one...
      </div>  
      ';
    }
  } else {
    $new_contact = true;
    $error = '
      <div class="alert alert-warning">
        Invalid contact ID. You are creating a new one...
      </div>  
      ';
  }
} else {
  $new_contact = true;
}


if (!$new_contact) {

  $client_options = '<option selected disabled value="">Select a client</option>';
  $order_by = " ORDER BY name ASC";
  $sql_client_read = "SELECT * FROM Clients " . $order_by;
  $result = $database->query($sql_client_read);
  $clients = $database->fetchAll($result);
  foreach ($clients as $client) {
    $check_condition = " WHERE contact_id = $contact_id";
    $check_condition .= " AND client_id = " . $client['client_id'];
    $check_condition .= " AND status = 1";

    $sql_client_check = "SELECT * FROM Client2Contact" . $check_condition;
    $result = $database->query($sql_client_check);
    $has_client = $database->has_results($result);
    if (!$has_client) {
      $client_options .= "<option value='" . encrypt_data($client['client_id']) . "'>" . $client['name'] . "</option>";
    }
  } 

  $client_tab_content = '
  <!-- CLIENTS TAB -->
  <div class="tab-pane fade" id="clients">
  <!-- Modal -->
<div class="modal fade" id="clientSelectorModal" tabindex="-1" aria-labelledby="clientSelectorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="clientSelectorModalLabel">Select Client</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Select a client to link to this contact.</p>
        <select class="form-select" aria-label="Select client" id="clientSelector">
        ' . $client_options . '
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
    <div class="d-flex justify-content-between mb-3">
      <h5>Linked Clients</h5>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#clientSelectorModal">Link Client</button>
    </div>
    ';

  $condition = " WHERE contact_id = " . $contact_id;
  $condition .= " AND c2c.client_id IS NOT NULL";
  $condition .= " AND `status` = 1";

  $inner_join = " INNER JOIN Clients AS c ON c2c.client_id = c.client_id";

  $order_by = " ORDER BY c.name ASC";
  $sql_client_read = "SELECT * FROM Client2Contact AS c2c " . $inner_join . $condition . $order_by;
  $result = $database->query($sql_client_read);
  if ($database->has_results($result)) {
    $clients = $database->fetchAll($result);
    $client_tab_content .= '
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Client Name</th>
          <th>Client Code</th>
          <th></th>
        </tr>
      </thead>
      <tbody>';

    foreach ($clients as $client) {
      $client_id_enc = encrypt_data($client['client_id']);
      $client_tab_content .= '
        <tr>
          <td>' . $client['name'] . '</td>
          <td>' . $client['client_code'] . '</td>
          <td>
            <a href="/contacts/unlink/?id=' . $contact_id_enc . '&client_id=' . $client_id_enc . '" class="text-danger">Unlink</a>
          </td>
        </tr>';
    }

    $client_tab_content .= '
        </tbody>
        </table>
      </div>
    ';
  } else {
      $client_tab_content .= '
      <div class="alert alert-secondary">
        No clients found for this contact.
      </div>
    </div>
    ';
  }

  $client_tab = '
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#clients">
            Client(s)
            </button>
          </li>
';
} else {
  $client_tab_content = '';
  $client_tab = '';
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
      <h3>Contact</h3>
      {$error}
      <div id="feedback-container" class="alert d-none">
        <p id="feedback-message"></p>
      </div>
      <form id="contactForm">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">
            General
            </button>
          </li>
          {$client_tab}
        </ul>
        <div class="tab-content border border-top-0 p-3">
          <!-- GENERAL TAB -->
          <div class="tab-pane fade show active" id="general">
            <input type="hidden" name="contact_id" value="{$contact_id_enc}" id="contact_id">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" value="{$contact_name}" id="contact_name">
            </div>
            <div class="mb-3">
              <label class="form-label">Surname</label>
              <input type="text" name="surname" class="form-control" value="{$contact_surname}" id="contact_surname">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="text" name="email" class="form-control" value="{$contact_email}" id="contact_email">
            </div>
          </div>
          {$client_tab_content}
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a href="/contacts" class="btn btn-secondary">Back to List</a>
        </div>
      </div>
      </form>
    </div>
    $bottom_scripts
    <script src="/assets/js/editContact.js"></script>
  </body>
</html>
HTML;
