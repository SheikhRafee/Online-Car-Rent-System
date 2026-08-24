<?php

require_once __DIR__ . "/../models/db.php";

// ---- error message holders ----
$nameError     = "";
$emailError    = "";
$passwordError = "";
$confirmError  = "";
$addressError  = "";
$phoneError    = "";
$roleError     = "";
$hasError      = "";

// ---- sticky values, so a failed submit doesn't wipe the form ----
$name    = "";
$email   = "";
$address = "";
$phone   = "";
$role    = "";

$allowedRoles = ["admin", "member"];

if (isset($_POST["mysubmit"])) {

    $name     = trim($_POST["name"]     ?? "");
    $email    = trim($_POST["email"]    ?? "");
    $password =      $_POST["password"] ?? "";
    $confirm  =      $_POST["confirm"]  ?? "";
    $address  = trim($_POST["address"]  ?? "");
    $phone    = trim($_POST["phone"]    ?? "");
    $role     =      $_POST["role"]     ?? "";

    // ---- name ----
    if ($name === "") {
        $nameError = "Name must not be empty";
        $hasError  = "1";
    } elseif (strlen($name) > 100) {
        $nameError = "Name must be 100 characters or fewer";
        $hasError  = "1";
    }

    // ---- email ----
    if ($email === "") {
        $emailError = "Email must not be empty";
        $hasError   = "1";
    } elseif (!preg_match('/^\S+@\S+\.\S+$/', $email)) {
        $emailError = "Enter a valid email address";
        $hasError   = "1";
    } else {
        // Format is fine — now check the database to make sure
        // nobody has already registered with this email.
        $db   = new mydb();
        $conn = $db->openConn();

        if ($db->emailExists($conn, $email)) {
            $emailError = "An account with this email already exists";
            $hasError   = "1";
        }

        $conn->close();
    }

    // ---- password ----
    if ($password === "") {
        $passwordError = "Password must not be empty";
        $hasError      = "1";
    } elseif (strlen($password) < 8) {
        $passwordError = "Password must be at least 8 characters";
        $hasError      = "1";
    }

    // ---- confirm password ----
    if ($confirm === "") {
        $confirmError = "Please confirm your password";
        $hasError     = "1";
    } elseif ($password !== $confirm) {
        $confirmError = "Passwords do not match";
        $hasError     = "1";
    }

    // ---- address ----
    if ($address === "") {
        $addressError = "Address must not be empty";
        $hasError     = "1";
    }

    // ---- phone ----
    if ($phone === "") {
        $phoneError = "Phone must not be empty";
        $hasError   = "1";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $phoneError = "Enter a valid phone number";
        $hasError   = "1";
    }

    // ---- role (whitelist) ----
    if ($role === "") {
        $roleError = "Please select a role";
        $hasError  = "1";
    } elseif (!in_array($role, $allowedRoles, true)) {
        $roleError = "Invalid role selected";
        $hasError  = "1";
    }

    // ---- all clear ----
    if ($hasError === "") {
        $db   = new mydb();
        $conn = $db->openConn();

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $success = $db->insertUser($conn, $name, $email, $passwordHash, $address, $phone, $role);

        $conn->close();

        if ($success) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            // Reusing $emailError rather than adding a new variable —
            // keeps registration.php's view completely untouched.
            // Very rare in practice (the DB going down mid-request).
            $emailError = "Something went wrong creating your account. Please try again.";
            $hasError   = "1";
        }
    }
}