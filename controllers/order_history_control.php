<?php
include "session_check.php";
include "../models/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

$status   = $_GET["status"] ?? "";
$dateFrom = $_GET["date_from"] ?? "";
$dateTo   = $_GET["date_to"] ?? "";

$allowedStatus = ["", "pending", "confirmed", "cancelled"];
if (!in_array($status, $allowedStatus)) {
    $status = "";
}

$result = $mydb->getAllOrders($status, $dateFrom, $dateTo, $conobj);
?>
