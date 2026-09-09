<?php
/* =====================================================================
   views/invoice.php
   ---------------------------------------------------------------------
   Shows the order the member has just placed, and offers two choices:

       Cancel order     -> the order's status becomes 'cancelled'
       Finalise & pay   -> on to the payment page

   BOTH BUTTONS ARE FORMS, NOT LINKS.
   That is deliberate. A link sends a GET, and a GET is supposed to
   only look at things. Anything that CHANGES data must be a POST, so
   it cannot be triggered by pasting a URL, by a browser prefetching a
   page, or by an image tag on somebody else's website.

   controllers/invoice_control.php has already given us $order and $days.
   ===================================================================== */

require_once __DIR__ . "/../controllers/invoice_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoice #<?php echo $order["id"]; ?> &mdash; Car Rental</title>

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

            <a href="homepage.php" class="back-link">&larr; Back to all cars</a>

            <section class="invoice-card">

                <!-- ========== HEADING ========== -->

                <div class="invoice-head">

                    <div>

                        <h1>Invoice</h1>

                        <p class="invoice-ref">
                            Order #<?php echo $order["id"]; ?>
                            &middot;
                            <!--
                                The database stores the date as
                                "2026-08-31 14:52:35". strtotime turns
                                that into a number, and date() prints
                                that number in a friendlier shape.
                            -->
                            placed <?php echo date("d M Y", strtotime($order["order_date"])); ?>
                        </p>

                    </div>

                    <!-- The status also becomes a CSS class, so
                         "pending" is amber, "confirmed" green and
                         "cancelled" grey. See public/css/style.css. -->
                    <span class="badge badge-<?php echo htmlspecialchars($order["status"]); ?>">
                        <?php echo htmlspecialchars($order["status"]); ?>
                    </span>

                </div>

                <!-- ========== CAR ========== -->

                <div class="invoice-car">

                    <?php $imageFile = "../public/uploads/cars/" . $order["image_path"]; ?>

                    <?php if ($order["image_path"] != "" && file_exists($imageFile)) { ?>

                        <img
                            src="<?php echo htmlspecialchars($imageFile); ?>"
                            alt="<?php echo htmlspecialchars($order["car_name"]); ?>"
                        >

                    <?php } ?>

                    <div>

                        <h2><?php echo htmlspecialchars($order["car_name"]); ?></h2>

                        <p class="detail-type"><?php echo htmlspecialchars($order["type"]); ?></p>

                        <p class="car-model">Model: <?php echo htmlspecialchars($order["model"]); ?></p>

                    </div>

                </div>

                <!-- ========== COST BREAKDOWN ========== -->

                <div class="summary">

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
                        <span>Daily rate</span>
                        <span><?php echo number_format($order["price_per_day"]); ?> BDT</span>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total payable</span>
                        <span><?php echo number_format($order["total_cost"]); ?> BDT</span>
                    </div>

                </div>

                <p class="note note-left">
                    Nothing has been charged yet. Finalising takes you to the
                    payment step, where you choose how to pay.
                </p>

                <!-- ========== THE TWO BUTTONS ========== -->
                <!--
                    Two separate little forms, both posting to the same
                    controller. They send the SAME order_id but a
                    DIFFERENT action, and order_control.php decides what
                    to do based on that action.
                -->

                <div class="action-row">

                    <form method="post" action="../controllers/order_control.php"
                          onsubmit="return confirmCancel();">

                        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $order["id"]; ?>">
                        <input type="hidden" name="action" value="cancel">

                        <button type="submit" class="secondary-button">Cancel order</button>

                    </form>

                    <form method="post" action="../controllers/order_control.php">

                        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $order["id"]; ?>">
                        <input type="hidden" name="action" value="finalize">

                        <button type="submit" class="primary-button">Finalise &amp; pay</button>

                    </form>

                </div>

            </section>

        </div>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

    <script src="../public/js/validation.js"></script>

</body>

</html>
