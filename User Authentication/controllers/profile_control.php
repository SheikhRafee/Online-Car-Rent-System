<?php

require_once __DIR__ . "/../models/db.php";


// -------------------------------------------------
// GET USER ID
// -------------------------------------------------

$userId = 0;

if (isset($_GET["user_id"])) {

    $userId = (int) $_GET["user_id"];

} elseif (isset($_POST["user_id"])) {

    $userId = (int) $_POST["user_id"];
}


// If no user ID was provided
if ($userId <= 0) {

    die("User ID is missing.");
}


// -------------------------------------------------
// DATABASE
// -------------------------------------------------

$db = new mydb();

$conn = $db->openConn();

$userRow = $db->findUserById($conn, $userId);


if (!$userRow) {

    $conn->close();

    die("User not found.");
}


// -------------------------------------------------
// VARIABLES
// -------------------------------------------------

$nameError = "";
$emailError = "";
$addressError = "";
$phoneError = "";
$pictureError = "";
$profileError = "";
$profileSuccess = "";

$currentPasswordError = "";
$newPasswordError = "";
$confirmPasswordError = "";
$passwordSuccess = "";

$name = $userRow["name"];
$email = $userRow["email"];
$address = $userRow["address"] ?? "";
$phone = $userRow["phone"] ?? "";


// -------------------------------------------------
// PROFILE PICTURE SETTINGS
// -------------------------------------------------

$allowedPictureTypes = [

    "image/jpeg" => "jpg",

    "image/png" => "png"
];

$maxPictureBytes = 2 * 1024 * 1024;


// -------------------------------------------------
// UPDATE PROFILE
// -------------------------------------------------

if (isset($_POST["update_profile"])) {

    $name = trim($_POST["name"] ?? "");

    $email = trim($_POST["email"] ?? "");

    $address = trim($_POST["address"] ?? "");

    $phone = trim($_POST["phone"] ?? "");

    $hasError = "";


    // NAME

    if ($name === "") {

        $nameError = "Name must not be empty";

        $hasError = "1";

    } elseif (strlen($name) > 100) {

        $nameError =
            "Name must be 100 characters or fewer";

        $hasError = "1";
    }


    // EMAIL

    if ($email === "") {

        $emailError =
            "Email must not be empty";

        $hasError = "1";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $emailError =
            "Enter a valid email address";

        $hasError = "1";

    } elseif (
        $db->emailTakenByOther(
            $conn,
            $email,
            $userId
        )
    ) {

        $emailError =
            "Another account already uses this email";

        $hasError = "1";
    }


    // ADDRESS

    if ($address === "") {

        $addressError =
            "Address must not be empty";

        $hasError = "1";
    }


    // PHONE

    if ($phone === "") {

        $phoneError =
            "Phone must not be empty";

        $hasError = "1";

    } elseif (
        !preg_match(
            '/^[0-9+\-\s]{7,20}$/',
            $phone
        )
    ) {

        $phoneError =
            "Enter a valid phone number";

        $hasError = "1";
    }


    // -------------------------------------------------
    // PROFILE IMAGE
    // -------------------------------------------------

    $pendingUpload = null;


    if (
        !empty($_FILES["profile_picture"]) &&
        $_FILES["profile_picture"]["error"] !==
        UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES["profile_picture"];


        if ($file["error"] !== UPLOAD_ERR_OK) {

            $pictureError =
                "There was a problem uploading that file";

            $hasError = "1";


        } elseif ($file["size"] > $maxPictureBytes) {

            $pictureError =
                "Image must be 2MB or smaller";

            $hasError = "1";


        } else {

            $mimeType =
                mime_content_type(
                    $file["tmp_name"]
                );


            if (!isset($allowedPictureTypes[$mimeType])) {

                $pictureError =
                    "Only JPEG or PNG images are allowed";

                $hasError = "1";

            } else {

                $pendingUpload = [

                    "tmp_name" =>
                        $file["tmp_name"],

                    "extension" =>
                        $allowedPictureTypes[$mimeType]
                ];
            }
        }
    }


    // -------------------------------------------------
    // SAVE PROFILE IMAGE
    // -------------------------------------------------

    $newPictureFilename = null;


    if (
        $hasError === "" &&
        $pendingUpload !== null
    ) {

        $newPictureFilename =
            "user_" .
            $userId .
            "_" .
            time() .
            "." .
            $pendingUpload["extension"];


        $destination =
            __DIR__ .
            "/../public/uploads/profiles/" .
            $newPictureFilename;


        if (
            !move_uploaded_file(
                $pendingUpload["tmp_name"],
                $destination
            )
        ) {

            $pictureError =
                "There was a problem saving that file";

            $hasError = "1";

            $newPictureFilename = null;
        }
    }


    // -------------------------------------------------
    // UPDATE DATABASE
    // -------------------------------------------------

    if ($hasError === "") {

        $success = $db->updateProfile(
            $conn,
            $userId,
            $name,
            $email,
            $address,
            $phone,
            $newPictureFilename
        );


        if ($success) {

            $userRow =
                $db->findUserById(
                    $conn,
                    $userId
                );

            $profileSuccess =
                "Profile updated.";

        } else {

            $profileError =
                "Something went wrong saving your profile. Please try again.";
        }
    }
}


// -------------------------------------------------
// CHANGE PASSWORD
// -------------------------------------------------

if (isset($_POST["change_password"])) {

    $currentPassword =
        $_POST["current_password"] ?? "";

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";

    $hasError = "";


    // CURRENT PASSWORD

    if ($currentPassword === "") {

        $currentPasswordError =
            "Enter your current password";

        $hasError = "1";

    } elseif (
        !password_verify(
            $currentPassword,
            $userRow["password_hash"]
        )
    ) {

        $currentPasswordError =
            "Current password is incorrect";

        $hasError = "1";
    }


    // NEW PASSWORD

    if ($newPassword === "") {

        $newPasswordError =
            "Enter a new password";

        $hasError = "1";

    } elseif (strlen($newPassword) < 8) {

        $newPasswordError =
            "Password must be at least 8 characters";

        $hasError = "1";
    }


    // CONFIRM PASSWORD

    if ($confirmPassword === "") {

        $confirmPasswordError =
            "Please confirm your new password";

        $hasError = "1";

    } elseif (
        $newPassword !== $confirmPassword
    ) {

        $confirmPasswordError =
            "Passwords do not match";

        $hasError = "1";
    }


    // UPDATE PASSWORD

    if ($hasError === "") {

        $newHash =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        $success =
            $db->updatePassword(
                $conn,
                $userId,
                $newHash
            );


        if ($success) {

            $passwordSuccess =
                "Password changed.";

            $userRow =
                $db->findUserById(
                    $conn,
                    $userId
                );

        } else {

            $currentPasswordError =
                "Something went wrong changing your password. Please try again.";
        }
    }
}


$conn->close();