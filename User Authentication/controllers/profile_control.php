<?php

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";

$currentUser = requireLogin();

$db   = new mydb();
$conn = $db->openConn();

// Always start from what's actually in the database, not just the session,
// so the form shows the current address/phone/profile picture too.
$userRow = $db->findUserById($conn, $currentUser["id"]);

// ---- error / success holders ----
$nameError     = "";
$emailError    = "";
$addressError  = "";
$phoneError    = "";
$pictureError  = "";
$profileError  = "";
$profileSuccess = "";

$currentPasswordError = "";
$newPasswordError     = "";
$confirmPasswordError = "";
$passwordSuccess      = "";

// ---- sticky values for the profile form ----
$name    = $userRow["name"];
$email   = $userRow["email"];
$address = $userRow["address"] ?? "";
$phone   = $userRow["phone"]   ?? "";

$allowedPictureTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
];
$maxPictureBytes = 2 * 1024 * 1024; // 2MB

// ------------------------------------------------------------------
// Update profile details
// ------------------------------------------------------------------
if (isset($_POST["update_profile"])) {

    $name    = trim($_POST["name"]    ?? "");
    $email   = trim($_POST["email"]   ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone   = trim($_POST["phone"]   ?? "");

    $hasError = "";

    if ($name === "") {
        $nameError = "Name must not be empty";
        $hasError  = "1";
    } elseif (strlen($name) > 100) {
        $nameError = "Name must be 100 characters or fewer";
        $hasError  = "1";
    }

    if ($email === "") {
        $emailError = "Email must not be empty";
        $hasError   = "1";
    } elseif (!preg_match('/^\S+@\S+\.\S+$/', $email)) {
        $emailError = "Enter a valid email address";
        $hasError   = "1";
    } elseif ($db->emailTakenByOther($conn, $email, $currentUser["id"])) {
        $emailError = "Another account already uses this email";
        $hasError   = "1";
    }

    if ($address === "") {
        $addressError = "Address must not be empty";
        $hasError     = "1";
    }

    if ($phone === "") {
        $phoneError = "Phone must not be empty";
        $hasError   = "1";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $phoneError = "Enter a valid phone number";
        $hasError   = "1";
    }

    // ---- optional profile picture ----
    // Validate now, but don't touch disk yet — if name/email/address/phone
    // above already failed, moving the upload here would leave an orphaned
    // file with nothing in the database pointing at it.
    $pendingUpload = null;

    if (!empty($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES["profile_picture"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $pictureError = "There was a problem uploading that file";
            $hasError     = "1";
        } elseif ($file["size"] > $maxPictureBytes) {
            $pictureError = "Image must be 2MB or smaller";
            $hasError     = "1";
        } else {
            $mimeType = mime_content_type($file["tmp_name"]);

            if (!isset($allowedPictureTypes[$mimeType])) {
                $pictureError = "Only JPEG or PNG images are allowed";
                $hasError     = "1";
            } else {
                $pendingUpload = [
                    "tmp_name"  => $file["tmp_name"],
                    "extension" => $allowedPictureTypes[$mimeType],
                ];
            }
        }
    }

    $newPictureFilename = null;

    if ($hasError === "" && $pendingUpload !== null) {
        $newPictureFilename = "user_" . $currentUser["id"] . "_" . time() . "." . $pendingUpload["extension"];
        $destination        = __DIR__ . "/../public/uploads/profiles/" . $newPictureFilename;

        if (!move_uploaded_file($pendingUpload["tmp_name"], $destination)) {
            $pictureError       = "There was a problem saving that file";
            $hasError           = "1";
            $newPictureFilename = null;
        }
    }

    if ($hasError === "") {
        $success = $db->updateProfile($conn, $currentUser["id"], $name, $email, $address, $phone, $newPictureFilename);

        if ($success) {
            // Keep the session in sync (nav "Welcome, ..." reads from it).
            $_SESSION["name"]  = $name;
            $_SESSION["email"] = $email;

            $userRow         = $db->findUserById($conn, $currentUser["id"]);
            $profileSuccess  = "Profile updated.";
        } else {
            $profileError = "Something went wrong saving your profile. Please try again.";
        }
    }
}

// ------------------------------------------------------------------
// Change password
// ------------------------------------------------------------------
if (isset($_POST["change_password"])) {

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword     = $_POST["new_password"]     ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    $hasError = "";

    if ($currentPassword === "") {
        $currentPasswordError = "Enter your current password";
        $hasError              = "1";
    } elseif (!password_verify($currentPassword, $userRow["password_hash"])) {
        $currentPasswordError = "Current password is incorrect";
        $hasError              = "1";
    }

    if ($newPassword === "") {
        $newPasswordError = "Enter a new password";
        $hasError          = "1";
    } elseif (strlen($newPassword) < 8) {
        $newPasswordError = "Password must be at least 8 characters";
        $hasError          = "1";
    }

    if ($confirmPassword === "") {
        $confirmPasswordError = "Please confirm your new password";
        $hasError              = "1";
    } elseif ($newPassword !== $confirmPassword) {
        $confirmPasswordError = "Passwords do not match";
        $hasError              = "1";
    }

    if ($hasError === "") {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = $db->updatePassword($conn, $currentUser["id"], $newHash);

        if ($success) {
            $passwordSuccess = "Password changed.";
            $userRow         = $db->findUserById($conn, $currentUser["id"]);
        } else {
            $currentPasswordError = "Something went wrong changing your password. Please try again.";
        }
    }
}

$conn->close();
