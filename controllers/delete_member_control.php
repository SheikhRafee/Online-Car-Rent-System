<?php
/**
 * admin/control/delete_member_control.php
 *
 * Same idea as sir's search_control.php being called directly by
 * XMLHttpRequest from myjs.js (myajax() -> "../control/search_control.php?search=..").
 * The assignment doc requires this AJAX endpoint to return JSON
 * (Content-Type: application/json), so — unlike search_control.php's
 * plain echo — this one builds a JSON response.
 */

include "session_check.php";
include "../models/db.php";

header("Content-Type: application/json");

$mydb   = new mydb();
$conobj = $mydb->openConn();

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $response["message"] = "Method not allowed.";
    echo json_encode($response);
    exit;
}

if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
    $response["message"] = "Invalid session token.";
    echo json_encode($response);
    exit;
}

$id = (int) ($_POST["id"] ?? 0);

$result = $mydb->findMemberById($id, $conobj);
if ($result->num_rows == 0) {
    $response["message"] = "Member not found.";
    echo json_encode($response);
    exit;
}

// deleting the user cascades to their orders and blogs (schema ON DELETE CASCADE)
$ok = $mydb->deleteMember($id, $conobj);

if ($ok) {
    $response["success"] = true;
    $response["message"] = "Member deleted successfully.";
} else {
    $response["message"] = "Could not delete member.";
}

echo json_encode($response);
?>
