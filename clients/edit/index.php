<?php
$init = require_once($_SERVER['DOCUMENT_ROOT'] . '/config/init.php');
extract($init); // Creates $system_title, $top_scripts, $bottom_scripts & etc.
$database = $init['db'];

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
      <!-- <div class="d-flex justify-content-between align-items-center mb-3"> -->
      <h3>Client</h3>
      <form method="POST">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">
            General
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#contacts">
            Contact(s)
            </button>
          </li>
        </ul>
        <div class="tab-content border border-top-0 p-3">
          <!-- GENERAL TAB -->
          <div class="tab-pane fade show active" id="general">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Client Code</label>
              <input type="text" name="client_code" class="form-control" readonly>
            </div>
          </div>
          <!-- CONTACTS TAB -->
          <div class="tab-pane fade" id="contacts">
            <div class="d-flex justify-content-between mb-3">
              <h5>Linked Contacts</h5>
              <button class="btn btn-sm btn-primary">Link Contact</button>
            </div>
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Smith John</td>
                  <td>john@email.com</td>
                  <td>
                    <a href="#" class="text-danger">Unlink</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a href="/clients" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
    </form>
    <!-- </div> -->
    </div>
    $bottom_scripts
  </body>
</html>
HTML;
