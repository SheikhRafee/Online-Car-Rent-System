<?php
include "session_check.php";
include "../model/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

$result = $mydb->getAllMembers($conobj);
?>
