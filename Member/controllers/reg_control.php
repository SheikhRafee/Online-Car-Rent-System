<?php

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------
// ALREADY LOGGED IN? no need to register again
// -----------------------------

ensureLoggedIn();

if (isset($_SESSION["user_id"])) {
    header("Location: homepage.php");
    exit;
}

// -----------------------------
// Fields + error placeholders — these must exist BEFORE the form
// renders, since registration.php echoes every one of them on a
// plain GET request (first visit to the page), not just after POST.
// -----------------------------

$name    = "";
$email   = "";
$address = "";
$phone   = "";
$role    = "";

$nameError     = "";
$emailError    = "";
$passwordError = "";
$confirmError  = "";
$addressError  = "";
$phoneError    = "";
$roleError     = "";
$hasError      = "";

if (isset($_POST["mysubmit"])) {

    $name     = trim($_POST["name"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm"] ?? "";
    $address  = trim($_POST["address"] ?? "");
    $phone    = trim($_POST["phone"] ?? "");
    $role     = $_POST["role"] ?? "";

    // -----------------------------
    // NAME
    // -----------------------------

    if ($name === "") {
        $nameError = "Name must not be empty";
        $hasError  = "1";
    } elseif (strlen($name) > 100) {
        $nameError = "Name must be 100 characters or fewer";
        $hasError  = "1";
    }

    // -----------------------------
    // EMAIL
    // -----------------------------

    if ($email === "") {
        $emailError = "Email must not be empty";
        $hasError   = "1";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Enter a valid email address";
        $hasError   = "1";
    }

    // -----------------------------
    // PASSWORD
    // -----------------------------

    if ($password === "") {
        $passwordError = "Password must not be empty";
        $hasError      = "1";
    } elseif (strlen($password) < 8) {
        $passwordError = "Password must be at least 8 characters";
        $hasError      = "1";
    }

    // -----------------------------
    // CONFIRM PASSWORD
    // -----------------------------

    if ($confirm === "") {
        $confirmError = "Please confirm your password";
        $hasError     = "1";
    } elseif ($password !== $confirm) {
        $confirmError = "Passwords do not match";
        $hasError     = "1";
    }

    // -----------------------------
    // ADDRESS
    // -----------------------------

    if ($address === "") {
        $addressError = "Address must not be empty";
        $hasError     = "1";
    }

    // -----------------------------
    // PHONE
    // -----------------------------

    if ($phone === "") {
        $phoneError = "Phone must not be empty";
        $hasError   = "1";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $phoneError = "Enter a valid phone number";
        $hasError   = "1";
    }

    // -----------------------------
    // ROLE
    // -----------------------------

    if ($role === "" || !in_array($role, ["member", "admin"], true)) {
        $roleError = "Please select a role";
        $hasError  = "1";
    }

    // -----------------------------
    // DATABASE — unique email check + insert
    // -----------------------------

    if ($hasError === "") {

        $db   = new mydb();
        $conn = $db->openConn();

        if ($db->emailExists($conn, $email)) {

            $emailError = "An account with this email already exists";
            $hasError   = "1";
            $conn->close();

        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $success = $db->insertUser(
                $conn,
                $name,
                $email,
                $passwordHash,
                $address,
                $phone,
                $role
            );

            $conn->close();

            if ($success) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $hasError   = "1";
                $emailError = "Registration failed — please try again";
            }
        }
    }
}