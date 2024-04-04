<?php

function generate_main_key(): string
{
    return openssl_random_pseudo_bytes(32);
}

function generate_secondary_key(): string
{
    return openssl_random_pseudo_bytes(16);
}

function encrypt(string $plaintext, string $key, string $iv): false|string
{
    return openssl_encrypt($plaintext, "AES-256-CBC", $key, 0, $iv);
}

function decrypt(string $ciphertext, string $key, string $iv): false|string
{
    return openssl_decrypt($ciphertext, "AES-256-CBC", $key, 0, $iv);
}

function encrypt_main_key(string $password): array
{
    $key_string = base64_encode(generate_main_key()); // This is the key that will be used to encrypt the user's data
    $salt = generate_secondary_key();
    $iv = generate_secondary_key();

    $user_key = hash_pbkdf2("sha3-256", $password, $salt, 100000, 32);
    $encrypted_key = encrypt($key_string, $user_key, $iv);

    return [
        "key" => $encrypted_key,
        "salt" => base64_encode($salt),
        "iv" => base64_encode($iv)
    ];
}

function decrypt_main_key(string $password, string $encrypted_key, string $salt, string $iv): false|string
{
    $salt = base64_decode($salt);
    $iv = base64_decode($iv);

    $user_key = hash_pbkdf2("sha3-256", $password, $salt, 100000, 32);
    return decrypt($encrypted_key, $user_key, $iv);
}
