<?php
include "session_check.php";
include "../model/db.php";

$nameError        = "";
$modelError       = "";
$typeError        = "";
$priceError       = "";
$imageError       = "";
$hasError         = "";

$name   = "";
$model  = "";
$type   = "";
$price  = "";
$availability = "available";
$description  = "";

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

    // image upload — same move_uploaded_file() pattern as sir's reg_control.php
    $imageName = "";
    if (!empty($_FILES["myfile"]["name"])) {

        $allowed = ["image/jpeg", "image/png"];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES["myfile"]["type"], $allowed)) {
            $imageError = "Only JPEG or PNG images are allowed";
            $hasError = "1";
        } elseif ($_FILES["myfile"]["size"] > $maxSize) {
            $imageError = "Image must be 2MB or smaller";
            $hasError = "1";
        } else {
            $imageName = time() . "_" . basename($_FILES["myfile"]["name"]);
            if (move_uploaded_file($_FILES["myfile"]["tmp_name"], "../uploads/cars/" . $imageName)) {
                // uploaded fine
            } else {
                $imageError = "Cannot upload image";
                $hasError = "1";
            }
        }
    }

    if ($hasError == "") {
        $mydb   = new mydb();
        $conobj = $mydb->openConn();
        $ok = $mydb->insertCar($name, $model, $type, $price, $availability, $imageName, $description, $conobj);

        if ($ok) {
            $_SESSION["flash_success"] = "Car added successfully.";
            header("Location: ../view/car_list.php");
            exit;
        } else {
            echo $conobj->error;
        }
    }
}
?>
