<?php
/* =====================================================================
   views/car_details.php
   ---------------------------------------------------------------------
   One car, plus the form that books it.

   controllers/car_control.php has already given us:
       $car             the car's row from the database
       $carId           its id
       $pricePerDay     its daily rate
       $startDate       what the user typed (so the form remembers)
       $endDate
       $startDateError  a message, or "" when that field was fine
       $endDateError
       $orderError      a message about the car itself

   The little summary box (Collection / Return / Days / Total) is filled
   in by JavaScript using AJAX - see js/validation.js. That is why its
   values are dashes here: PHP does not know the dates yet.
   ===================================================================== */

require_once __DIR__ . "/../controllers/car_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($car["name"]); ?> &mdash; Car Rental</title>

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

        <a href="homepage.php" class="back-link">&larr; Back to all cars</a>

        <!--
            The error summary. It is printed only when at least one of
            the three error variables actually holds a message.
        -->

        <?php if ($startDateError != "" || $endDateError != "" || $orderError != "") { ?>

            <div class="alert">

                <strong>Your order could not be placed.</strong>

                <ul>
                    <?php if ($orderError != "")     { ?><li><?php echo htmlspecialchars($orderError);     ?></li><?php } ?>
                    <?php if ($startDateError != "") { ?><li><?php echo htmlspecialchars($startDateError); ?></li><?php } ?>
                    <?php if ($endDateError != "")   { ?><li><?php echo htmlspecialchars($endDateError);   ?></li><?php } ?>
                </ul>

            </div>

        <?php } ?>

        <section class="detail-layout">

            <!-- ========== CAR INFORMATION ========== -->

            <article class="detail-card">

                <div class="detail-image">

                    <?php $imageFile = "../public/uploads/cars/" . $car["image_path"]; ?>

                    <?php if ($car["image_path"] != "" && file_exists($imageFile)) { ?>

                        <img
                            src="<?php echo htmlspecialchars($imageFile); ?>"
                            alt="<?php echo htmlspecialchars($car["name"]); ?>"
                        >

                    <?php } else { ?>

                        <div class="no-image">No image yet</div>

                    <?php } ?>

                </div>

                <div class="detail-body">

                    <?php if ($car["availability_status"] == "available") { ?>
                        <span class="badge badge-available">Available</span>
                    <?php } else { ?>
                        <span class="badge badge-unavailable">Unavailable</span>
                    <?php } ?>

                    <h1><?php echo htmlspecialchars($car["name"]); ?></h1>

                    <p class="detail-type"><?php echo htmlspecialchars($car["type"]); ?></p>

                    <p class="description"><?php echo htmlspecialchars($car["description"]); ?></p>

                    <dl class="spec-list">

                        <div class="spec">
                            <dt>Model year</dt>
                            <dd><?php echo htmlspecialchars($car["model"]); ?></dd>
                        </div>

                        <div class="spec">
                            <dt>Category</dt>
                            <dd><?php echo htmlspecialchars($car["type"]); ?></dd>
                        </div>

                        <div class="spec">
                            <dt>Daily rate</dt>
                            <dd><?php echo number_format($pricePerDay); ?> BDT</dd>
                        </div>

                        <div class="spec">
                            <dt>Insurance</dt>
                            <dd>Included</dd>
                        </div>

                    </dl>

                </div>

            </article>

            <!-- ========== ORDER FORM ========== -->

            <aside class="order-panel">

                <h2>Book this car</h2>

                <form class="order-form" method="post" action="" id="orderForm"
                      onsubmit="return validateOrderForm();" novalidate>

                    <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">

                    <!--
                        The car id travels with the POST. Without it the
                        controller would not know which car the dates
                        belong to, because a POST carries no URL query.
                    -->
                    <input type="hidden" name="car_id" id="carId" value="<?php echo $carId; ?>">

                    <div class="form-group<?php if ($startDateError != "") { echo " invalid"; } ?>">

                        <label for="start_date">Collection date</label>

                        <input type="date" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($startDate); ?>">

                        <span class="error" id="startDateError"><?php echo htmlspecialchars($startDateError); ?></span>

                    </div>

                    <div class="form-group<?php if ($endDateError != "") { echo " invalid"; } ?>">

                        <label for="end_date">Return date</label>

                        <input type="date" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($endDate); ?>">

                        <span class="error" id="endDateError"><?php echo htmlspecialchars($endDateError); ?></span>

                    </div>

                    <!--
                        THE LIVE SUMMARY.
                        These four spans start as dashes. Every time a
                        date changes, JavaScript asks ajax_cost.php for
                        the days and the total and writes the answers in
                        here - with no page reload.
                    -->

                    <div class="summary">

                        <div class="summary-row">
                            <span>Collection</span>
                            <span id="sOut">&mdash;</span>
                        </div>

                        <div class="summary-row">
                            <span>Return</span>
                            <span id="sIn">&mdash;</span>
                        </div>

                        <div class="summary-row">
                            <span>Days</span>
                            <span id="sDays">&mdash;</span>
                        </div>

                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="sTotal">&mdash;</span>
                        </div>

                    </div>

                    <button type="submit" class="primary-button">Place order</button>

                    <p class="note">
                        You will see an invoice before anything is confirmed.
                    </p>

                </form>

            </aside>

        </section>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

    <script src="../public/js/validation.js"></script>

</body>

</html>
