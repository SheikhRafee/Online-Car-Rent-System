<?php


include "session_check.php";
include "../models/admin_db.php";

header("Content-Type: application/json");

$mydb   = new admin_db();
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

$ok = $mydb->deleteMember($id, $conobj);

if ($ok) {
    $response["success"] = true;
    $response["message"] = "Member deleted successfully.";
} else {
    $response["message"] = "Could not delete member.";
}

echo json_encode($response);
?>
