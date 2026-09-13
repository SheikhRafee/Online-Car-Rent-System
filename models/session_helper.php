<?php
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION["user_id"])) {
        if (isset($_POST["ajax"])) {
            http_response_code(401);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "error" => "Not logged in"]);
            exit;
        }
        header("Location: /login.php");
        exit;
    }

    return [
        "id"   => $_SESSION["user_id"],
        "name" => $_SESSION["name"],
        "role" => $_SESSION["role"],
    ];
}

function getToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function checkToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $sent = $_POST["csrf_token"] ?? '';
    if (empty($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $sent)) {
        if (isset($_POST["ajax"])) {
            http_response_code(403);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "error" => "Invalid CSRF token"]);
            exit;
        }
        http_response_code(403);
        die("Invalid request.");
    }
}