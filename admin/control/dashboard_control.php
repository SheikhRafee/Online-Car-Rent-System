<?php
include "session_check.php";
include "../model/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

$total_cars    = $mydb->countCars($conobj);
$total_members = $mydb->countMembers($conobj);
$total_orders  = $mydb->countOrders($conobj);
$total_blogs   = $mydb->countBlogs($conobj);
?>
