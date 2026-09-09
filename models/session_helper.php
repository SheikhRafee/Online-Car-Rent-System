<?php

require_once __DIR__ . "/../config/db_config.php";
require_once __DIR__ . "/db.php";

const REMEMBER_ME_COOKIE = "car_rental_remember";
const REMEMBER_ME_DAYS   = 30;

function ensureLoggedIn()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION["user_id"])) {
        return;
    }

    if (!isset($_COOKIE[REMEMBER_ME_COOKIE])) {
        return;
    }

    $parts = explode(":", $_COOKIE[REMEMBER_ME_COOKIE], 2);
    if (count($parts) !== 2) {
        return;
    }

    [$userId, $signature] = $parts;

    if (!ctype_digit($userId)) {
        return;
    }

    $expected = hash_hmac("sha256", $userId, REMEMBER_ME_SECRET);
    if (!hash_equals($expected, $signature)) {
        return;
    }

    $db   = new mydb();
    $conn = $db->openConn();
    $user = $db->findUserById($conn, (int) $userId);
    $conn->close();

    if (!$user) {
        return;
    }

    logInUser($user);
}

function logInUser($user)
{
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"]    = $user["name"];
    $_SESSION["email"]   = $user["email"];
    $_SESSION["role"]    = $user["role"];
}

function setRememberMeCookie($userId)
{
    $signature = hash_hmac("sha256", (string) $userId, REMEMBER_ME_SECRET);
    $value     = $userId . ":" . $signature;

    setcookie(REMEMBER_ME_COOKIE, $value, [
        "expires"  => time() + (REMEMBER_ME_DAYS * 86400),
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

function clearRememberMeCookie()
{
    setcookie(REMEMBER_ME_COOKIE, "", [
        "expires"  => time() - 3600,
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

function requireLogin()
{
    ensureLoggedIn();

    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    return [
        "id"    => $_SESSION["user_id"],
        "name"  => $_SESSION["name"],
        "email" => $_SESSION["email"],
        "role"  => $_SESSION["role"],
    ];
}