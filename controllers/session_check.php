<?php


session_start();

if (empty($_SESSION["user_id"]) || empty($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../views/login.php");
    exit;
}


if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>
