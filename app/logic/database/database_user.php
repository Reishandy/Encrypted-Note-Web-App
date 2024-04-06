<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../encryption.php';

function sign_up(string $username, string $password): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Check if the user already exists
    $check_statement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $check_statement->bind_param("s", $username);

    $check_statement->execute();
    $result = $check_statement->get_result();

    if ($result->num_rows > 0) {
        return ["status" => "occupied", "message" => "Username already exists"];
    }

    // Create and encrypt the main key
    $encrypted_key_array = encrypt_main_key($password);

    // Prepare the data to be inserted
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
    $encrypted_key = $encrypted_key_array["key"];
    $salt = $encrypted_key_array["salt"];
    $iv = $encrypted_key_array["iv"];

    // Insert the data
    $insert_statement = $dbh->prepare("INSERT INTO users (username, password, main_key, salt, iv) VALUES (?, ?, ?, ?, ?)");
    $insert_statement->bind_param("sssss", $username, $hashed_password, $encrypted_key, $salt, $iv);

    $insert_statement->execute();

    // Create user's data table
    $table_statement = $dbh->prepare("CREATE TABLE $username (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        title VARCHAR(255) NOT NULL,
        note TEXT NOT NULL,
        iv VARCHAR(24) NOT NULL
    )");
    $table_statement->execute();

    $dbh->close();

    return ["status" => "sign_up_success", "message" => "User added successfully"];
}

function sign_in(string $username, string $password): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Check if the user exists
    $check_statement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $check_statement->bind_param("s", $username);

    $check_statement->execute();
    $result = $check_statement->get_result();

    if ($result->num_rows === 0) {
        return ["status" => "not_found", "message" => "Username not found"];
    }

    // Get the user's data
    $user = $result->fetch_assoc();
    $hashed_password = $user["password"];
    $encrypted_key = $user["main_key"];
    $salt = $user["salt"];
    $iv = $user["iv"];

    // Verify the password
    if (!password_verify($password, $hashed_password)) {
        return ["status" => "incorrect_password", "message" => "Incorrect password"];
    }

    // Return the decrypted main key
    return ["status" => "sign_in_success", "message" => "Signed-in successfully", "key" => decrypt_main_key($password, $encrypted_key, $salt, $iv)];
}