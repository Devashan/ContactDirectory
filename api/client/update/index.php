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

$client_id_enc = $database->sanitize($data['client_id']); // Because it technically comes from the GET params in the frontend URL
$client_name = $database->sanitize($data['client_name']);
$contact_id_array = $data['contact_id'] ?? [];

if (empty($client_name)) {
    echo json_encode(['error' => 'Client name is required', 'status' => 400]);
    exit;
}

$client_id = decrypt_data($client_id_enc);
if ($client_id > 0) {
    // Update client
    $values = "name = ?";
    $params = [$client_name];
    $param_types = "s";

    $sql_update = "UPDATE Clients SET $values WHERE client_id = " . $client_id;
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

    if (!empty($contact_id_array)) {
        foreach ($contact_id_array as $contact_id_enc) {
            $contact_id = decrypt_data($contact_id_enc);

            $check_condition = "client_id = ? AND contact_id = ?";
            $check_params = [$client_id, $contact_id];
            $check_param_types = "ii";

            $check_sql = "SELECT c_c_id FROM Client2Contact WHERE $check_condition";
            $check_stmt = $database->prepare($check_sql);
            if ($check_stmt == false) {
                echo json_encode(['error' => 'Failed to prepare check statement', 'status' => 500]);
                exit;
            }
            $check_stmt->bind_param($check_param_types, ...$check_params);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $check_row = $check_result->fetch_assoc();
                $c_c_id = $check_row['c_c_id'];

                $values = "status = 1";
                $values .= ", updated_at = '$now'";

                $sql_update = "UPDATE Client2Contact SET $values WHERE c_c_id = $c_c_id";
                $database->query($sql_update);
                if ($database->affected_rows() == 0) {
                    echo json_encode(['error' => 'Failed to update contact', 'status' => 500]);
                    exit;
                }
            } else {

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
    }
} else {


    $client_code = generateClientCode($client_name, $database);

    $columns = "name";
    $values = "?";
    $params = [$client_name];
    $param_types = "s";

    if (!empty($client_code)) {
        $columns .= ", client_code";
        $values .= ", ?";
        $params[] = $client_code;
        $param_types .= "s";
    }

    $columns .= ", created_at";
    $values .= ", '$now'";

    $columns .= ", created_by";
    $values .= ", 1"; // Since auth isnt included, lets force 1 for the gees.

    $sql_insert = "INSERT INTO Clients ($columns) VALUES ($values)";
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

    $client_id = $database->insert_id();
    $client_id_enc = encrypt_data($client_id);
}
$database->close();
echo json_encode(['success' => true, 'status' => 200]);
exit;
