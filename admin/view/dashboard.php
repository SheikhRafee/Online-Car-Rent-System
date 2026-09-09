<?php
include "../control/dashboard_control.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" type="text/css" href="../css/admin_car.css">
</head>
<body>

<?php include "nav.php"; ?>

<h1>Admin Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <h2><?php echo $total_cars; ?></h2>
        <p>Total Cars</p>
    </div>
    <div class="stat-card">
        <h2><?php echo $total_members; ?></h2>
        <p>Total Members</p>
    </div>
    <div class="stat-card">
        <h2><?php echo $total_orders; ?></h2>
        <p>Total Orders</p>
    </div>
    <div class="stat-card">
        <h2><?php echo $total_blogs; ?></h2>
        <p>Total Blog Posts</p>
    </div>
</div>

</body>
</html>
