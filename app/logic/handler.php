<?php

require_once __DIR__ . '/database/database_user.php';
require_once __DIR__ . '/database/database_note.php';

// Get the type of request, and call the appropriate function
$type = $_POST["type"];
switch ($type) {
    case "check_session":
        session_check();
        break;

    case "sign_in":
        sign_in_handler();
        break;

    case "sign_up":
        sign_up_handler();
        break;

    case "sign_out":
        sign_out();
        break;

    case "add_note":
        add_note_handler();
        break;

    case "get_notes":
        get_notes_handler();
        break;

    case "delete_note":
        delete_note_handler();
        break;

    case "update_note":
        update_note_handler();
        break;

    default:
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Invalid request type"]);
}


function session_check(): void
{
    session_start();

    header("Content-Type: application/json");
    if (isset($_SESSION["key"]) && isset($_SESSION["username"])) {
        echo json_encode(["status" => "authenticated"]);
    } else {
        echo json_encode(["status" => "not_authenticated"]);
    }
}

function sign_in_handler(): void
{
    // Sanitize the input
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    // Handle the response
    $response = sign_in($username, $password);
    if ($response["status"] === "sign_in_success") {
        // Remove the main key from the response, and store it in the session
        $main_key = $response["key"];
        unset($response["key"]);

        ini_set("session.gc_maxlifetime", 60 * 60 * 3); // session lasts for 3 hours
        session_start();
        session_regenerate_id(true);

        $_SESSION["key"] = $main_key;
        $_SESSION["username"] = $username;
    }

    header("Content-Type: application/json");
    echo json_encode($response);
}

function sign_up_handler(): void
{
    // Sanitize the input
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    // Additional validation using regex [a-zA-Z0-9_]+
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Invalid username"]);
        return;
    }

    // Get and send the response
    $response = sign_up($username, $password);

    header("Content-Type: application/json");
    echo json_encode($response);
}

function sign_out(): void
{
    session_start();
    session_destroy();
    header("Content-Type: application/json");
    echo json_encode(["status" => "sign_out_success"]);
}

function add_note_handler(): void
{
    session_start();

    // Sanitize the input
    $title = filter_var($_POST["title"], FILTER_SANITIZE_STRING);
    $note = filter_var($_POST["note"], FILTER_SANITIZE_STRING);

    // Get and send the response
    $response = add_note($_SESSION["username"], $_SESSION["key"], $title, $note);

    header("Content-Type: application/json");
    echo json_encode($response);
}

function get_notes_handler(): void
{
    session_start();

    // Get and send the response
    $response = get_notes($_SESSION["username"], $_SESSION["key"]);

    header("Content-Type: application/json");
    echo json_encode($response);
}

function delete_note_handler(): void
{
    session_start();

    // Sanitize the input
    $note_id = filter_var($_POST["note_id"], FILTER_SANITIZE_NUMBER_INT);

    // Get and send the response
    $response = delete_note($_SESSION["username"], $note_id);

    header("Content-Type: application/json");
    echo json_encode($response);
}

function update_note_handler(): void
{
    session_start();

    // Sanitize the input
    $note_id = filter_var($_POST["note_id"], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST["title"], FILTER_SANITIZE_STRING);
    $note = filter_var($_POST["note"], FILTER_SANITIZE_STRING);

    // Get and send the response
    $response = update_note($_SESSION["username"], $_SESSION["key"], $note_id, $title, $note);

    header("Content-Type: application/json");
    echo json_encode($response);
}