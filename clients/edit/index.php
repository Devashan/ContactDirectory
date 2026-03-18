<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = DB::create_instance();

$client_id_enc = $_GET['id'] ?? null;
if (!empty($client_id_enc)) {
  $client_id = decrypt_data($client_id_enc);
  if ($client_id > 0) {
    $new_client = false;
    $condition = " WHERE client_id = " . $client_id;
    $sql_client_read = "SELECT * FROM Clients" . $condition;

    $result = $database->query($sql_client_read);
    if ($database->has_results($result)) {
      $client = $database->fetchOne($result);

      $client_id_enc = encrypt_data($client['client_id']);
      $client_name = $client['name'];
      $client_code = $client['client_code'];
    } else {
      $error = '
      <div class="alert alert-warning">
        No client(s) found. You are creating a new one...
      </div>  
      ';
    }
  } else {
    $new_client = true;
    $error = '
      <div class="alert alert-warning">
        Invalid client ID. You are creating a new one...
      </div>  
      ';
  }
} else {
  $new_client = true;
}


if (!$new_client) {

  $contact_options = '<option selected disabled value="">Select a contact</option>';
  $order_by = " ORDER BY surname ASC, name ASC";
  $sql_contacts_read = "SELECT * FROM Contacts " . $order_by;
  $result = $database->query($sql_contacts_read);
  $contacts = $database->fetchAll($result);
  foreach ($contacts as $contact) {
    $sql_contact_check = "SELECT * FROM Client2Contact WHERE client_id = $client_id  AND contact_id = $contact[contact_id]";
    $result = $database->query($sql_contact_check);
    $has_contact = $database->has_results($result);
    if (!$has_contact) {
      $contact_full_name = $contact['name'] . ' ' . $contact['surname'];
      $contact_options .= "<option value='" . encrypt_data($contact['contact_id']) . "'>" . $contact_full_name . "</option>";
    }
  }

  $contact_tab_content = '
  <!-- CONTACTS TAB -->
  <div class="tab-pane fade" id="contacts">
  <!-- Modal -->
<div class="modal fade" id="contactSelectorModal" tabindex="-1" aria-labelledby="contactSelectorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="contactSelectorModalLabel">Select Contact</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Select a contact to link to this client.</p>
        <select class="form-select" aria-label="Select contact" id="contactSelector">
        ' . $contact_options . '
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
      <h5>Linked Contacts</h5>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#contactSelectorModal">Link Contact</button>
    </div>
    ';

  $condition = " WHERE client_id = " . $client_id;
  $condition .= " AND c2c.contact_id IS NOT NULL";
  $condition .= " AND `status` = 1";

  $inner_join = " INNER JOIN Contacts AS c ON c2c.contact_id = c.contact_id";

  $order_by = " ORDER BY c.surname ASC, c.name ASC";
  $sql_client_read = "SELECT * FROM Client2Contact AS c2c " . $inner_join . $condition . $order_by;
  $result = $database->query($sql_client_read);
  if ($database->has_results($result)) {
    $users = $database->fetchAll($result);
    $contact_tab_content .= '
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Contact Full Name</th>
          <th>Contact email address</th>
          <th></th>
        </tr>
      </thead>
      <tbody>';

    foreach ($users as $user) {
      $contact_full_name = $user['surname'] . ' ' . $user['name'];
      $contact_id_enc = encrypt_data($user['contact_id']);
      $contact_tab_content .= '
        <tr>
          <td>' . $contact_full_name . '</td>
          <td>' . $user['email'] . '</td>
          <td>
            <a href="/clients/unlink/?id=' . $client_id_enc . '&contact_id=' . $contact_id_enc . '" class="text-danger">Unlink</a>
          </td>
        </tr>';
    }

    $contact_tab_content .= '
        </tbody>
        </table>
      </div>
    ';
  } else {
      $contact_tab_content .= '
      <div class="alert alert-secondary">
        No contacts found for this client.
      </div>
    </div>
    ';
  }

  $contact_tab = '
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#contacts">
            Contact(s)
            </button>
          </li>
';
} else {
  $contact_tab_content = '';
  $contact_tab = '';
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
      <h3>Client</h3>
      {$error}
      <div id="feedback-container" class="alert d-none">
        <p id="feedback-message"></p>
      </div>
      <form id="clientForm">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">
            General
            </button>
          </li>
          {$contact_tab}
        </ul>
        <div class="tab-content border border-top-0 p-3">
          <!-- GENERAL TAB -->
          <div class="tab-pane fade show active" id="general">
            <input type="hidden" name="client_id" value="{$client_id_enc}" id="client_id">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" value="{$client_name}" id="client_name">
            </div>
            <div class="mb-3">
              <label class="form-label">Client Code</label>
              <input type="text" name="client_code" class="form-control" readonly value="{$client_code}" id="client_code">
            </div>
          </div>
          {$contact_tab_content}
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a href="/" class="btn btn-secondary">Back to List</a>
        </div>
      </div>
      </form>
    </div>
    $bottom_scripts
    <script src="/assets/js/editClient.js"></script>
  </body>
</html>
HTML;
