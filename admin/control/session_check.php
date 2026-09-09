<?php
/**
 * admin/control/session_check.php
 *
 * Same idea as sir's profile_control.php:
 *     session_start();
 *     if (empty($_SESSION["uname"])) { header("Location: ../view/login.php"); }
 *
 * Task 1 is the one that actually creates the session on login and sets
 * $_SESSION['user_id'], $_SESSION['name'], $_SESSION['role'] (per the
 * assignment doc). This file is included at the top of every admin
 * control file and just checks that session, redirecting out if the
 * person isn't logged in as an admin.
 */

session_start();

if (empty($_SESSION["user_id"]) || empty($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}

// simple CSRF token, same idea sir uses for session values ($_SESSION["uname"])
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>
