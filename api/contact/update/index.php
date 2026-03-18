<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$database = DB::create_instance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$contact_id_enc = $database->sanitize($data['contact_id']); // Because it technically comes from the GET params in the frontend URL
$contact_name = $database->sanitize($data['contact_name']);
$contact_surname = $database->sanitize($data['contact_surname']);
$contact_email = $database->sanitize($data['contact_email']);
$client_id_array = $data['client_id'] ?? [];

if (empty($contact_name)) {
    echo json_encode(['error' => 'Contact name is required', 'status' => 400]);
    exit;
}

$contact_id = decrypt_data($contact_id_enc);
if ($contact_id > 0) {
    // Update client
    $values = "name = ?";
    $params = [$contact_name];
    $param_types = "s";

    $values .= ", surname = ?";
    $params[] = $contact_surname;
    $param_types .= "s";

    $values .= ", email = ?";
    $params[] = $contact_email;
    $param_types .= "s";

    $sql_update = "UPDATE Contacts SET $values WHERE contact_id = " . $contact_id;
    $stmt = $database->prepare($sql_update);
    if ($stmt == false) {
        echo json_encode(['error' => 'Failed to prepare statement', 'status' => 500]);
        exit;
    }
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    if ($stmt->error) {
        echo json_encode(['error' => 'Failed to execute statement', 'status' => 500]);
        exit;
    }

    if (!empty($client_id_array)) {   
        foreach ($client_id_array as $client_id_enc) {
            $client_id = decrypt_data($client_id_enc);

            $columns = "client_id";
            $values = "?";
            $params = [$client_id];
            $param_types = "i";

            $columns .= ", contact_id";
            $values .= ", ?";
            $params[] = $contact_id;
            $param_types .= "i";
            
            $columns .= ", created_at";
            $values .= ", '$now'";
            
            $columns .= ", created_by";
            $values .= ", 1"; // TODO: Get current user ID when implemented

            $columns .= ", `status`";
            $values .= ", 1";

            $sql_insert = "INSERT INTO Client2Contact ($columns) VALUES ($values)";
            $stmt = $database->prepare($sql_insert);
            if ($stmt == false) {
                echo json_encode(['error' => 'Failed to prepare statement', 'status' => 500]);
                exit;
            }
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            if ($stmt->error) {
                echo json_encode(['error' => 'Failed to execute statement', 'status' => 500]);
                exit;
            }
        }
    }
} else {

    $columns = "name";
    $values = "?";
    $params = [$contact_name];
    $param_types = "s";
    
    $columns .= ", surname";
    $values .= ", ?";
    $params[] = $contact_surname;
    $param_types .= "s";
   
    $columns .= ", email";
    $values .= ", ?";
    $params[] = $contact_email;
    $param_types .= "s";

    $columns .= ", created_at";
    $values .= ", '$now'";

    $columns .= ", created_by";
    $values .= ", 1"; // Since auth isnt included, lets force 1 for the gees.

    $sql_insert = "INSERT INTO Contacts ($columns) VALUES ($values)";
    $stmt = $database->prepare($sql_insert);
    if ($stmt == false) {
        echo json_encode(['error' => 'Failed to prepare statement', 'status' => 500]);
        exit;
    }
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    if ($stmt->error) {
        echo json_encode(['error' => 'Failed to execute statement', 'status' => 500]);
        exit;
    }

    $contact_id = $database->insert_id();
    $contact_id_enc = encrypt_data($contact_id);
}

echo json_encode(['success' => true, 'status' => 200]);
exit;

