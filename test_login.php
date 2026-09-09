<?php


session_start();

$adminId = 2; 

$_SESSION['user_id'] = $adminId;
$_SESSION['name']    = 'Admin';
$_SESSION['role']    = 'admin';

header('Location: admin/view/dashboard.php');
exit;
