<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {

    $delete_id = $_POST["delete_id"];

    $sql = "DELETE FROM blogs WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting post: " . $stmt->error;
    }

    $stmt->close();
}
?>