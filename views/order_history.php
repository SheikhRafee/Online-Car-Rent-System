<?php
include "../controllers/order_history_control.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Order History</title>
<link rel="stylesheet" type="text/css" href="../css/admin_car.css">
</head>
<body>

<?php include "nav.php"; ?>

<h1>All Rent Order History</h1>

<form action="" method="get">
    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="">All</option>
        <option value="pending" <?php echo ($status == "pending") ? "selected" : ""; ?>>Pending</option>
        <option value="confirmed" <?php echo ($status == "confirmed") ? "selected" : ""; ?>>Confirmed</option>
        <option value="cancelled" <?php echo ($status == "cancelled") ? "selected" : ""; ?>>Cancelled</option>
    </select>

    <label for="date_from">From:</label>
    <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">

    <label for="date_to">To:</label>
    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">

    <input type="submit" value="Filter">
    <a href="order_history.php">Reset</a>
</form>

<table class="admin-table" border="1" cellpadding="6">
<tr>
    <th>Order ID</th>
    <th>Member</th>
    <th>Car</th>
    <th>Rental Period</th>
    <th>Total Cost</th>
    <th>Status</th>
    <th>Payment Method</th>
    <th>Order Date</th>
</tr>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td>#<?php echo (int) $row["id"]; ?></td>
        <td><?php echo htmlspecialchars($row["member_name"]); ?><br><small><?php echo htmlspecialchars($row["member_email"]); ?></small></td>
        <td><?php echo htmlspecialchars($row["car_name"]); ?> (<?php echo htmlspecialchars($row["car_model"]); ?>) - <?php echo htmlspecialchars($row["car_type"]); ?></td>
        <td><?php echo htmlspecialchars($row["start_date"]); ?> to <?php echo htmlspecialchars($row["end_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["total_cost"]); ?></td>
        <td><?php echo htmlspecialchars($row["status"]); ?></td>
        <td><?php echo htmlspecialchars($row["payment_method"] ?? "-"); ?></td>
        <td><?php echo htmlspecialchars($row["order_date"]); ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="8">No orders found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>
