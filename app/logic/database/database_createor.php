<?php

require_once __DIR__ . '/../config.php';

/**
 * Return indicator (string) of the table creation status:
 *
 * - Error: Failed to connect to MySQL error message
 * - "occupied": Table already exists
 * - "success": Table created successfully
 */
function users_table_creator(): string
{
    // Handle the database connection and errors
    global $dbh;
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return "Failed to connect to MySQL: " . $dbh->connect_error;
    }

    // Check if the table already exists
    $query = "SHOW TABLES LIKE 'users'";
    $result = $dbh->query($query);
    if ($result->num_rows > 0) {
        return "occupied";
    }

    // Create the table
    $query = "CREATE TABLE users (
        username VARCHAR(255) PRIMARY KEY,
        password VARCHAR(97) NOT NULL,
        main_key VARCHAR(64) NOT NULL,
        salt VARCHAR(24) NOT NULL,
        iv VARCHAR(24) NOT NULL
    )";
    $dbh->query($query);
    $dbh->close();

    return "success";
}