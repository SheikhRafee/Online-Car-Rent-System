<?php
/**
 * Shared session / "Remember me" logic.
 *
 * Every page that requires a logged-in user should:
 *   require_once "../models/session_helper.php";
 *   $currentUser = requireLogin();
 *
 * "Remember me" is implemented as a signed cookie rather than a database
 * token table, because the shared schema (users/cars/orders/payments/
 * rentals/blogs) has no table for login tokens and the project rules say
 * not to add to or alter the shared schema. The cookie holds the user id
 * plus an HMAC signature (using REMEMBER_ME_SECRET) so it can't be forged
 * or edited by the browser; it only ever grants the same session a normal
 * login would.
 */

require_once __DIR__ . "/../config/db_config.php";
require_once __DIR__ . "/db.php";

const REMEMBER_ME_COOKIE = "car_rental_remember";
const REMEMBER_ME_DAYS   = 30;

/** Start the session (if not already started) and log the user in from
 *  their session, or, failing that, from a valid "remember me" cookie. */
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

/** Populate $_SESSION for a user row returned by the db model. */
function logInUser($user)
{
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"]    = $user["name"];
    $_SESSION["email"]   = $user["email"];
    $_SESSION["role"]    = $user["role"];
}

/** Set the signed "remember me" cookie for this user. */
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

/** Clear the "remember me" cookie (used on logout). */
function clearRememberMeCookie()
{
    setcookie(REMEMBER_ME_COOKIE, "", [
        "expires"  => time() - 3600,
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

/** Call at the top of any page that requires an authenticated user.
 *  Redirects to login.php and exits if nobody is logged in. */
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
