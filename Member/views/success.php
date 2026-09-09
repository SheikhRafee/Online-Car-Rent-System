<?php
/* =====================================================================
   views/success.php
   ---------------------------------------------------------------------
   The receipt. The last page of the booking journey.

   controllers/success_control.php has given us $order, $days and
   $reference, and has already made sure the order really is confirmed.
   ===================================================================== */

require_once __DIR__ . "/../controllers/success_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking Confirmed &mdash; Order #<?php echo $order["id"]; ?></title>

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
            <a href="order_history.php">Order History</a>
            <?php if (file_exists("blog.php")) { ?>
                <a href="blog.php">Blog</a>
            <?php } ?>
            <a href="profile.php">Profile</a>
            <a href="../controllers/login_control.php?logout=true">Logout</a>
        </nav>

    </header>

    <!-- ================= MAIN ================= -->

    <main class="rental-container">

        <div class="narrow">

            <section class="invoice-card">

                <!-- ========== CONFIRMATION ========== -->

                <div class="success-top">

                    <div class="success-mark">
                        <!-- A hand-drawn tick, as an SVG. The polyline
                             is just three points joined by a line. -->
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="3"
                             stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="4 12.5 9.5 18 20 6.5"></polyline>
                        </svg>
                    </div>

                    <h1>Booking confirmed</h1>

                    <p>Your car is reserved. A copy of this summary is in your order history.</p>

                </div>

                <!-- ========== SUMMARY ========== -->

                <div class="summary">

                    <div class="summary-row">
                        <span>Order number</span>
                        <span>#<?php echo $order["id"]; ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Car</span>
                        <span>
                            <?php echo htmlspecialchars($order["car_name"]); ?>
                            (<?php echo htmlspecialchars($order["model"]); ?>)
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>Collection date</span>
                        <span><?php echo date("d M Y", strtotime($order["start_date"])); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Return date</span>
                        <span><?php echo date("d M Y", strtotime($order["end_date"])); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Rental period</span>
                        <span><?php echo $days; ?> days</span>
                    </div>

                    <div class="summary-row">
                        <span>Payment method</span>
                        <!-- paymentMethodName() turns "cash_on_delivery"
                             into "Cash on delivery" - see models/helpers.php -->
                        <span><?php echo htmlspecialchars(paymentMethodName($order["payment_method"])); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Transaction</span>
                        <span><?php echo htmlspecialchars($reference); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Status</span>
                        <span class="badge badge-<?php echo htmlspecialchars($order["status"]); ?>">
                            <?php echo htmlspecialchars($order["status"]); ?>
                        </span>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total paid</span>
                        <span><?php echo number_format($order["total_cost"]); ?> BDT</span>
                    </div>

                </div>

                <!-- ========== WHERE TO NEXT ========== -->

                <div class="link-row">

                    <a href="order_history.php" class="link-secondary">View order history</a>

                    <a href="homepage.php" class="link-primary">Rent another car</a>

                </div>

            </section>

        </div>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

</body>

</html>
