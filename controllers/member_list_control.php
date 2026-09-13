<?php
include "session_check.php";
include "../models/admin_db.php";

$mydb   = new admin_db();
$conobj = $mydb->openConn();

$result = $mydb->getAllMembers($conobj);
?>
