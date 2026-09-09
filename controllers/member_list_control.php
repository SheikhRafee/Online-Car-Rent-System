<?php
include "session_check.php";
include "../models/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

$result = $mydb->getAllMembers($conobj);
?>
