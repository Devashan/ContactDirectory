<?php

require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/database.php';

function encrypt_data(string $plaintext, ?string $key = null): string
{
    if ($key === null) {
        $key = getenv('ENCRYPTION_KEY');
        if ($key === null) {
            throw new Exception('ENCRYPTION_KEY not found in environment variables');
        }
    }
    if (getenv('ENCRYPTION_IV') === false) {
        throw new Exception('ENCRYPTION_IV not found in environment variables');
    }
    $cipher = "AES-256-CBC";

    // use consistent IV from .env
    $iv = getenv('ENCRYPTION_IV');

    $encrypted = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
}

function decrypt_data(string $encrypted, ?string $key = null): string|false
{
    if ($key === null) {
        $key = getenv('ENCRYPTION_KEY');
        if ($key === null) {
            throw new Exception('ENCRYPTION_KEY not found in environment variables');
        }
    }
    if (getenv('ENCRYPTION_IV') === false) {
        throw new Exception('ENCRYPTION_IV not found in environment variables');
    }
    $cipher = "AES-256-CBC";

    $data = base64_decode(str_pad(strtr($encrypted, '-_', '+/'), strlen($encrypted) % 4, '=', STR_PAD_RIGHT));

    // use consistent IV from .env
    $iv = getenv('ENCRYPTION_IV');

    return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
}


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