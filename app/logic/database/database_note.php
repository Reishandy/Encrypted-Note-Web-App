<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../encryption.php';

function add_note(string $username, string $key, string $title, string $note): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Encrypt the note
    $iv = generate_secondary_key();
    $encrypted_title = encrypt($title, $key, $iv);
    $encrypted_note = encrypt($note, $key, $iv);
    $encoded_iv = base64_encode($iv);

    // Insert the data
    $insert_statement = $dbh->prepare("INSERT INTO $username (title, note, iv) VALUES (?, ?, ?)");
    $insert_statement->bind_param("sss", $encrypted_title, $encrypted_note, $encoded_iv);

    $insert_statement->execute();
    $dbh->close();

    return ["status" => "success", "message" => "Note added successfully"];
}

function get_notes(string $username, string $key): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Get the notes
    $select_statement = $dbh->prepare("SELECT * FROM $username");
    $select_statement->execute();

    $result = $select_statement->get_result();
    $notes = [];

    while ($row = $result->fetch_assoc()) {
        $iv = base64_decode($row["iv"]);
        $title = decrypt($row["title"], $key, $iv);
        $note = decrypt($row["note"], $key, $iv);

        $notes[] = [
            "id" => $row["id"],
            "date_created" => $row["date_created"],
            "date_modified" => $row["date_modified"],
            "title" => $title,
            "note" => $note
        ];
    }
    $dbh->close();

    if (empty($notes)) {
        return ["status" => "no_notes", "message" => "No notes found"];
    }

    return ["status" => "success", "message" => "Notes retrieved successfully", "notes" => $notes];
}

function update_note(string $username, string $key, int $id, string $title, string $note): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Encrypt the note
    $iv = generate_secondary_key();
    $encrypted_title = encrypt($title, $key, $iv);
    $encrypted_note = encrypt($note, $key, $iv);
    $encoded_iv = base64_encode($iv);

    // Update the data
    $update_statement = $dbh->prepare("UPDATE $username SET title = ?, note = ?, iv = ? WHERE id = ?");
    $update_statement->bind_param("sssi", $encrypted_title, $encrypted_note, $encoded_iv, $id);

    $update_statement->execute();
    $dbh->close();

    return ["status" => "success", "message" => "Note updated successfully"];

}

function delete_note(string $username, int $id): array
{
    // Handle the database connection and errors
    try {
        $dbh = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception) {
        return ["status" => "error", "message" => "Failed to connect to Database"];
    }

    // Delete the data
    $delete_statement = $dbh->prepare("DELETE FROM $username WHERE id = ?");
    $delete_statement->bind_param("i", $id);

    $delete_statement->execute();
    $dbh->close();

    return ["status" => "success", "message" => "Note deleted successfully"];
}