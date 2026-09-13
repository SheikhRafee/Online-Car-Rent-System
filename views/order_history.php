<?php
/* =====================================================================
   views/order_history.php
   ---------------------------------------------------------------------
   Every booking this member has made, newest first.

   controllers/history_control.php has given us $orders - an array where
   each item is one order joined to its car.

   The whole page is one foreach loop: for every order in the array,
   print one row of the table.
   ===================================================================== */

require_once __DIR__ . "/../controllers/history_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order History &mdash; Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css">

</head>

<body class="homepage">

    <!-- ================= HEADER ================= -->

    <header class="homepage-header">

        <div class="logo">
            Car<span>Rental</span>
        </div>

        <nav>
            <a href="homepage.php">Home</a>
            <?php if (file_exists("cars.php")) { ?>
                <a href="cars.php">Cars</a>
            <?php } ?>
            <a href="order_history.php" class="active">Order History</a>
            <?php if (file_exists("blog.php")) { ?>
                <a href="blog.php">Blog</a>
            <?php } ?>
            <a href="profile.php">Profile</a>
            <a href="../controllers/login_control.php?logout=true">Logout</a>
        </nav>

    </header>

    <!-- ================= MAIN ================= -->

    <main class="rental-container">

        <div class="history-head">
            <h1>Order history</h1>
            <p>Every car you have booked, newest first.</p>
        </div>

        <section class="history-card">

            <?php if (count($orders) > 0) { ?>

                <div class="table-scroll">

                    <table class="history-table">

                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Car</th>
                                <th>Collection</th>
                                <th>Return</th>
                                <th>Days</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($orders as $oneOrder) { ?>

                                <?php
                                // Worked out fresh for each row.
                                $rowDays = countDays($oneOrder["start_date"], $oneOrder["end_date"]);
                                ?>

                                <tr>
                                    <!--
                                        data-label is used by the mobile
                                        layout in public/css/style.css: on a narrow
                                        screen the table turns into stacked
                                        cards and CSS prints these labels
                                        in front of each value.
                                    -->
                                    <td data-label="Order">#<?php echo $oneOrder["id"]; ?></td>

                                    <td data-label="Car"><?php echo htmlspecialchars($oneOrder["car_name"]); ?></td>

                                    <td data-label="Collection"><?php echo date("d M Y", strtotime($oneOrder["start_date"])); ?></td>

                                    <td data-label="Return"><?php echo date("d M Y", strtotime($oneOrder["end_date"])); ?></td>

                                    <td data-label="Days"><?php echo $rowDays; ?></td>

                                    <td data-label="Payment"><?php echo htmlspecialchars(paymentMethodName($oneOrder["payment_method"])); ?></td>

                                    <td data-label="Total"><?php echo number_format($oneOrder["total_cost"]); ?> BDT</td>

                                    <td data-label="Status">
                                        <span class="badge badge-<?php echo htmlspecialchars($oneOrder["status"]); ?>">
                                            <?php echo htmlspecialchars($oneOrder["status"]); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            <?php } else { ?>

                <!-- Shown to a member who has never booked anything -->

                <div class="empty">
                    <h3>No bookings yet</h3>
                    <p>Once you rent a car it will appear here.</p>
                    <a href="homepage.php" class="link-primary">Browse cars</a>
                </div>

            <?php } ?>

        </section>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

</body>

</html>
