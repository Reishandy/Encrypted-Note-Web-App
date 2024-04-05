<?php

require_once __DIR__ . '/database/database_user.php';

// Get the type of request, and call the appropriate function
$type = $_POST["type"];
switch ($type) {
    case "sign-in":
        sign_in_handler();
        break;

    case "sign-up":
        sign_up_handler();
        break;

    case "check_session":
        session_check();
        break;

    case "sign_out":
        sign_out();
        break;

    default:
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Invalid request type"]);
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

function session_check(): void
{
    session_start();

    header("Content-Type: application/json");
    if (isset($_SESSION["key"])) {
        echo json_encode(["status" => "authenticated"]);
    } else {
        echo json_encode(["status" => "not_authenticated"]);
    }
}

function sign_out(): void
{
    session_start();
    session_destroy();
    header("Content-Type: application/json");
    echo json_encode(["status" => "sign_out_success"]);
}