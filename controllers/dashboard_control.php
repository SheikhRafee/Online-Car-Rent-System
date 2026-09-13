<?php
include "session_check.php";
include "../models/admin_db.php";

$mydb   = new admin_db();
$conobj = $mydb->openConn();

$total_cars    = $mydb->countCars($conobj);
$total_members = $mydb->countMembers($conobj);
$total_orders  = $mydb->countOrders($conobj);
$total_blogs   = $mydb->countBlogs($conobj);
?>
