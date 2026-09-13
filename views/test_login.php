<?php
session_start();


if (isset($_GET['action']) && $_GET['action'] == 'login') {
    $_SESSION['user_id'] = 2;
    $_SESSION['name'] = 'Admin';
    $_SESSION['role'] = 'admin';
    
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['logged_out'])) {
    echo "You have been logged out successfully!<br><br>";
}


echo '<a href="?action=login">Login as Admin</a>';
?>