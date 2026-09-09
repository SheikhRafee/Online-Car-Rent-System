<?php
include "session_check.php";
include "../model/db.php";

$mydb   = new mydb();
$conobj = $mydb->openConn();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

$result = $mydb->findCarById($id, $conobj);
if ($result->num_rows == 0) {
    $_SESSION["flash_error"] = "Car not found.";
    header("Location: ../view/car_list.php");
    exit;
}
$car = $result->fetch_assoc();

$nameError  = "";
$modelError = "";
$typeError  = "";
$priceError = "";
$imageError = "";
$hasError   = "";

$name  = $car["NAME"];
$model = $car["model"];
$type  = $car["TYPE"];
$price = $car["price_per_day"];
$availability = $car["availability_status"];
$description  = $car["description"];

if (isset($_REQUEST["mysubmit"])) {

    if (!hash_equals($_SESSION["csrf_token"], $_REQUEST["csrf_token"] ?? "")) {
        $hasError = "1";
    }

    $name  = trim($_REQUEST["name"]);
    $model = trim($_REQUEST["model"]);
    $type  = $_REQUEST["type"];
    $price = $_REQUEST["price_per_day"];
    $availability = $_REQUEST["availability_status"];
    $description  = trim($_REQUEST["description"]);

    if (empty($name)) {
        $nameError = "Car name must not be empty";
        $hasError = "1";
    }
    if (empty($model)) {
        $modelError = "Model must not be empty";
        $hasError = "1";
    }
    if (empty($type)) {
        $typeError = "Please select a car type";
        $hasError = "1";
    }
    if (!is_numeric($price) || $price <= 0) {
        $priceError = "Price per day must be a number greater than 0";
        $hasError = "1";
    }

    // optional new image — same upload pattern as add_car_control.php
    if (!empty($_FILES["myfile"]["name"])) {
        $allowed = ["image/jpeg", "image/png"];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($_FILES["myfile"]["type"], $allowed)) {
            $imageError = "Only JPEG or PNG images are allowed";
            $hasError = "1";
        } elseif ($_FILES["myfile"]["size"] > $maxSize) {
            $imageError = "Image must be 2MB or smaller";
            $hasError = "1";
        } elseif ($hasError == "") {
            $imageName = time() . "_" . basename($_FILES["myfile"]["name"]);
            if (move_uploaded_file($_FILES["myfile"]["tmp_name"], "../uploads/cars/" . $imageName)) {
                // delete old image file
                if (!empty($car["image_path"]) && file_exists("../uploads/cars/" . $car["image_path"])) {
                    unlink("../uploads/cars/" . $car["image_path"]);
                }
                $mydb->updateCarImage($id, $imageName, $conobj);
            } else {
                $imageError = "Cannot upload image";
                $hasError = "1";
            }
        }
    }

    if ($hasError == "") {
        $ok = $mydb->updateCar($id, $name, $model, $type, $price, $availability, $description, $conobj);
        if ($ok) {
            $_SESSION["flash_success"] = "Car updated successfully.";
            header("Location: ../view/car_list.php");
            exit;
        } else {
            echo $conobj->error;
        }
    }
}
?>
