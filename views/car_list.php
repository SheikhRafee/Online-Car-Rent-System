<?php
include "../controllers/car_list_control.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Cars</title>
<link rel="stylesheet" type="text/css" href="../css/admin_car.css">
</head>
<body>

<?php include "nav.php"; ?>

<h1>Manage Cars</h1>
<a href="add_car.php">+ Add New Car</a>

<?php if (!empty($_SESSION["flash_success"])): ?>
    <p class="flash-success"><?php echo htmlspecialchars($_SESSION["flash_success"]); unset($_SESSION["flash_success"]); ?></p>
<?php endif; ?>
<?php if (!empty($_SESSION["flash_error"])): ?>
    <p class="flash-error"><?php echo htmlspecialchars($_SESSION["flash_error"]); unset($_SESSION["flash_error"]); ?></p>
<?php endif; ?>

<table class="admin-table" border="1" cellpadding="6">
<tr>
    <th>Image</th>
    <th>Name</th>
    <th>Model</th>
    <th>Type</th>
    <th>Price/Day</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td>
            <?php if (!empty($row["image_path"])): ?>
                <img src="../uploads/cars/<?php echo htmlspecialchars($row["image_path"]); ?>" width="80">
            <?php else: ?>
                No image
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($row["NAME"]); ?></td>
        <td><?php echo htmlspecialchars($row["model"]); ?></td>
        <td><?php echo htmlspecialchars($row["TYPE"]); ?></td>
        <td><?php echo htmlspecialchars($row["price_per_day"]); ?></td>
        <td><?php echo htmlspecialchars($row["availability_status"]); ?></td>
        <td>
            <a href="edit_car.php?id=<?php echo (int) $row["id"]; ?>">Edit</a>
            |
            <form action="../control/delete_car_control.php" method="post" style="display:inline"
                  onsubmit="return confirm('Delete this car?');">
                <input type="hidden" name="id" value="<?php echo (int) $row["id"]; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
                <input type="submit" value="Delete">
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="7">No cars found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>
