<?php

require_once __DIR__ . "/../models/session_helper.php";
require_once __DIR__ . "/../models/blog_db.php";
require_once __DIR__ . "/../models/db.php";
$currentUser = requireLogin();

$blogDb = new mydb();
$conn = $blogDb->openConn();

$deleteError = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {

    checkToken();

    $deleteId = (int) $_POST["delete_id"];
    $post = findBlogById($deleteId, $conn);

    if ($post == null) {
        $deleteError = "That post no longer exists.";
    } elseif ($currentUser["role"] != "admin" && $post["user_id"] != $currentUser["name"]) {
        $deleteError = "You can only delete your own posts.";
    } else {
        deleteBlog($deleteId, $conn);

        if (isset($_POST["ajax"])) {
            header("Content-Type: application/json");
            echo json_encode(["success" => true]);
            exit;
        }

        header("Location: ../views/blog.php?deleted=1");
        exit;
    }


    if (isset($_POST["ajax"])) {
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "error" => $deleteError]);
        exit;
    }
}
?>