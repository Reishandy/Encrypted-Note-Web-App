<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../encryption.php';

/**
 * Return indicator (string) of the user addition status:
 *
 * - Error: Failed to connect to MySQL error message
 * - "occupied": Username already exists
 * - "success": User added successfully
 */
function add_user(string $username, string $password): string
{
    // Handle the database connection and errors
    global $dbh;
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return "Failed to connect to MySQL: " . $dbh->connect_error;
    }

    // Check if the user already exists
    $check_statement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $check_statement->bind_param("s", $username);

    $check_statement->execute();
    $result = $check_statement->get_result();

    if ($result->num_rows > 0) {
        return "occupied";
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
        data TEXT NOT NULL,
        iv VARCHAR(24) NOT NULL
    )");
    $table_statement->execute();

    $dbh->close();

    return "success";
}

/**
 * Return indicator (string) of the user retrieval status:
 *
 * - Error: Failed to connect to MySQL error message
 * - "not_found": Username not found
 * - "incorrect_password": Incorrect password
 * - Main key: User's main key (success)
 */
function get_user(string $username, string $password): string
{
    // Handle the database connection and errors
    global $dbh;
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return "Failed to connect to MySQL: " . $dbh->connect_error;
    }

    // Check if the user exists
    $check_statement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $check_statement->bind_param("s", $username);

    $check_statement->execute();
    $result = $check_statement->get_result();

    if ($result->num_rows === 0) {
        return "not_found";
    }

    // Get the user's data
    $user = $result->fetch_assoc();
    $hashed_password = $user["password"];
    $encrypted_key = $user["main_key"];
    $salt = $user["salt"];
    $iv = $user["iv"];

    // Verify the password
    if (!password_verify($password, $hashed_password)) {
        return "incorrect_password";
    }

    // Return the decrypted main key
    return decrypt_main_key($password, $encrypted_key, $salt, $iv);
}