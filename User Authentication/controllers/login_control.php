<?php

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- logout ----
if (isset($_GET["logout"]) && $_GET["logout"] === "true") {
    $_SESSION = [];
    session_destroy();
    clearRememberMeCookie();
    header("Location: login.php");
    exit;
}

// ---- already logged in (real session or a valid "remember me" cookie)? skip the form ----
// Only auto-login from the cookie when this is a plain page visit — if the
// user is actively submitting the login form (e.g. a stale cookie from a
// previous account on a shared computer), let that submission decide who
// ends up logged in instead of silently keeping the old session.
if (!isset($_POST["mysubmit"])) {
    ensureLoggedIn();

    if (isset($_SESSION["user_id"])) {
        header("Location: homepage.php");
        exit;
    }
}

$emailError    = "";
$passwordError = "";
$hasError      = "";
$email         = "";

if (isset($_POST["mysubmit"])) {

    $email    = trim($_POST["email"]    ?? "");
    $password =      $_POST["password"] ?? "";
    $remember =      isset($_POST["remember"]);

    if ($email === "") {
        $emailError = "Email must not be empty";
        $hasError   = "1";
    }

    if ($password === "") {
        $passwordError = "Password must not be empty";
        $hasError      = "1";
    }

    if ($hasError === "") {
        $db   = new mydb();
        $conn = $db->openConn();
        $user = $db->findUserByEmail($conn, $email);
        $conn->close();

        if (!$user || !password_verify($password, $user["password_hash"])) {
            // Deliberately vague — don't reveal whether the email exists.
            $emailError = "Incorrect email or password";
            $hasError   = "1";
        } else {
            logInUser($user);

            if ($remember) {
                setRememberMeCookie($user["id"]);
            }

            header("Location: homepage.php");
            exit;
        }
    }
}
