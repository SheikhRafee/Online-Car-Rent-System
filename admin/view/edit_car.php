<?php
include "../control/edit_car_control.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Car</title>
<link rel="stylesheet" type="text/css" href="../css/admin_car.css">
</head>
<body>

<?php include "nav.php"; ?>

<h1>Edit Car</h1>

<form action="" method="post" onsubmit="return myCarValidation()" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

    <label for="name">Car Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
    <?php echo $nameError; ?>
    <p id="name-error"></p><br><br>

    <label for="model">Model:</label>
    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($model); ?>">
    <?php echo $modelError; ?>
    <p id="model-error"></p><br><br>

    <label for="type">Type:</label>
    <select id="type" name="type">
        <option value="">-- Select Type --</option>
        <option value="Private Car" <?php echo ($type == "Private Car") ? "selected" : ""; ?>>Private Car</option>
        <option value="Microbus" <?php echo ($type == "Microbus") ? "selected" : ""; ?>>Microbus</option>
        <option value="Pick-up" <?php echo ($type == "Pick-up") ? "selected" : ""; ?>>Pick-up</option>
        <option value="SUV" <?php echo ($type == "SUV") ? "selected" : ""; ?>>SUV</option>
    </select>
    <?php echo $typeError; ?><br><br>

    <label for="price_per_day">Price Per Day:</label>
    <input type="text" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($price); ?>">
    <?php echo $priceError; ?>
    <p id="price-error"></p><br><br>

    <label for="availability_status">Availability:</label>
    <select id="availability_status" name="availability_status">
        <option value="available" <?php echo ($availability == "available") ? "selected" : ""; ?>>Available</option>
        <option value="unavailable" <?php echo ($availability == "unavailable") ? "selected" : ""; ?>>Unavailable</option>
    </select><br><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" rows="4" cols="40"><?php echo htmlspecialchars($description); ?></textarea><br><br>

    <?php if (!empty($car["image_path"])): ?>
        <img src="../uploads/cars/<?php echo htmlspecialchars($car["image_path"]); ?>" width="120"><br><br>
    <?php endif; ?>

    <label for="myfile">Replace Image (optional, JPEG/PNG, max 2MB):</label>
    <input type="file" id="myfile" name="myfile">
    <?php echo $imageError; ?><br><br>

    <input type="submit" name="mysubmit" value="Update Car">
</form>

<script src="../js/admin_car.js"></script>

</body>
</html>
