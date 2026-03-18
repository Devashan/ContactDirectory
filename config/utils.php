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

    // encrypt
    $encrypted = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    // return base64 encoded encrypted data
    return base64_encode($encrypted);
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

    $data = base64_decode($encrypted);

    // use consistent IV from .env
    $iv = getenv('ENCRYPTION_IV');

    return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
}

