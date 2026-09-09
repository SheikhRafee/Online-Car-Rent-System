<?php
include "session_check.php";
include "../models/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

if ($_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {

    $id = (int) $_POST["id"];

    $result = $mydb->findCarById($id, $conobj);
    if ($result->num_rows == 0) {
        $_SESSION["flash_error"] = "Car not found.";
    } elseif ($mydb->carHasActiveOrders($id, $conobj)) {
        // block deletion if the car has pending/confirmed orders
        $_SESSION["flash_error"] = "Cannot delete this car — it has pending or confirmed orders.";
    } else {
        $car = $result->fetch_assoc();
        if (!empty($car["image_path"]) && file_exists("../uploads/cars/" . $car["image_path"])) {
            unlink("../uploads/cars/" . $car["image_path"]);
        }
        $mydb->deleteCar($id, $conobj);
        $_SESSION["flash_success"] = "Car deleted successfully.";
    }
}

header("Location: ../views/car_list.php");
exit;
?>
