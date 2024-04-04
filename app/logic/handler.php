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

    default:
        echo json_encode(["status" => "error", "message" => "Invalid request type"]);
}


function sign_in_handler(): void
{
    // Sanitize the input
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    // Send the response
    $response = sign_in($username, $password);
    echo json_encode($response);
}

function sign_up_handler(): void
{
    // Sanitize the input
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    // Send the response
    $response = sign_up($username, $password);
    echo json_encode($response);
}