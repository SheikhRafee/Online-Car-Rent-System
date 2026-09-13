<?php

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------
// LOGOUT
// -----------------------------

if (isset($_GET["logout"]) && $_GET["logout"] === "true") {
    $_SESSION = [];
    session_destroy();
    clearRememberMeCookie();
    header("Location: login.php");
    exit;
}

// -----------------------------
// ALREADY LOGGED IN? skip the form and go straight to the homepage
// -----------------------------

if (!isset($_POST["mysubmit"])) {
    ensureLoggedIn();

    if (isset($_SESSION["user_id"])) {
        header("Location: homepage.php");
        exit;
    }
}

$emailError = "";
$passwordError = "";
$hasError = "";

$email = "";

if (isset($_POST["mysubmit"])) {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $remember = isset($_POST["remember"]);

    // -----------------------------
    // EMAIL VALIDATION
    // -----------------------------

    if ($email === "") {

        $emailError = "Email must not be empty";
        $hasError = "1";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $emailError = "Enter a valid email address";
        $hasError = "1";
    }


    // -----------------------------
    // PASSWORD VALIDATION
    // -----------------------------

    if ($password === "") {

        $passwordError = "Password must not be empty";
        $hasError = "1";
    }


    // -----------------------------
    // DATABASE LOGIN
    // -----------------------------

    if ($hasError === "") {

        $db = new mydb();

        $conn = $db->openConn();

        $user = $db->findUserByEmail($conn, $email);

        $conn->close();


        if (
            !$user ||
            !password_verify(
                $password,
                $user["password_hash"]
            )
        ) {

            $emailError = "Incorrect email or password";
            $hasError = "1";

        } else {

            // This is the part that was missing: actually populate
            // $_SESSION so requireLogin() on other pages recognizes you.
            logInUser($user);

            if ($remember) {
                setRememberMeCookie($user["id"]);
            }

            header("Location: homepage.php");

            exit;
        }
    }
}