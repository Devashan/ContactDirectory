<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/utils.php');
$database = DB::create_instance();

function generateClientCode($name, $database)
{
    $words = preg_split('/\s+/', strtoupper($name));

    if (count($words) > 1) {
        $letters = '';
        foreach ($words as $w) {
            $letters .= $w[0];
        }
        $letters = substr($letters, 0, 3);
    } else {
        $letters = substr($words[0], 0, 3);
    }

    $letters = str_pad($letters, 3, 'A');

    $num = 1;

    while (true) {
        $code = $letters . str_pad($num, 3, '0', STR_PAD_LEFT);

        $sql_check = "SELECT COUNT(*) FROM Clients WHERE client_code = ?";
        $stmt = $database->prepare($sql_check);
        $stmt->bind_param("s", $code);
        $stmt->execute();

        $result = $stmt->get_result();
        $count = $database->fetchOne($result)['COUNT(*)'];
                
        if ($count == 0) {
            return $code;
        }
        
        $num++;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$client_id_enc = $data['client_id'];
$client_name = $data['client_name'];

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

echo json_encode(['success' => true, 'status' => 200]);
exit;

