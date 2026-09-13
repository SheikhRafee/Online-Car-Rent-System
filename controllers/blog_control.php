<?php

require_once __DIR__ . "/../models/session_helper.php";
require_once __DIR__ . "/../models/blog_db.php";
require_once __DIR__ . "/../models/db.php";

$currentUser = requireLogin();
$blogDb = new mydb();
$conn = $blogDb->openConn();

$title = $content = "";
$titleError = $contentError = "";
$formIsValid = false;
$author = $currentUser["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    checkToken();
    $formIsValid = true;

    if (empty($_POST["title"])) {
        $titleError = "Title is required";
        $formIsValid = false;
    } elseif (!preg_match("/^[a-zA-Z0-9 ,.'\\-]{3,150}$/", $_POST["title"])) {
        $titleError = "3 to 150 characters: letters, numbers, spaces , . ' -";
        $formIsValid = false;
    } else {
        $title = test_value($_POST["title"]);
    }

    if (empty($_POST["content"])) {
        $contentError = "Content is required";
        $formIsValid = false;
    } else {
        $content = test_value($_POST["content"]);
    }

    if ($formIsValid) {
        if (insertBlog($currentUser["id"], $title, $content, $conn)) {
            $title = $content = ""; // Clear form fields
        } else {
            $formIsValid = false;
            $contentError = "Could not save post. Please try again.";
        }
    }
}

function test_value($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_POST["ajax"])) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => $formIsValid,
        "errors" => [
            "title"   => $titleError,
            "content" => $contentError
        ]
    ]);
    exit();
}