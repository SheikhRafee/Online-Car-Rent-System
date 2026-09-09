<?php
/* =====================================================================
   views/payment.php
   ---------------------------------------------------------------------
   Choose how to pay, and enter the reference number.

   controllers/payment_control.php has given us:
       $order             the order being paid for
       $days              its length
       $methodLabels      the five payment methods
       $paymentMethod     which radio was chosen (so it stays chosen)
       $transactionId     what was typed (so it stays typed)
       $methodError / $transactionError / $paymentError

   Notice the amount printed below comes from $order, not from any
   hidden field. The only things this form sends are the METHOD and the
   REFERENCE - never a price.
   ===================================================================== */

require_once __DIR__ . "/../controllers/payment_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment &mdash; Order #<?php echo $order["id"]; ?></title>

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

            <a href="invoice.php?id=<?php echo $order["id"]; ?>" class="back-link">
                &larr; Back to invoice
            </a>

            <section class="invoice-card">

                <!-- ========== HEADING ========== -->

                <div class="invoice-head">

                    <div>

                        <h1>Payment</h1>

                        <p class="invoice-ref">
                            Order #<?php echo $order["id"]; ?>
                            &middot;
                            <?php echo htmlspecialchars($order["car_name"]); ?>
                            &middot;
                            <?php echo $days; ?> days
                        </p>

                    </div>

                </div>

                <!-- ========== AMOUNT ========== -->

                <div class="amount-due">
                    <span>Amount due</span>
                    <strong><?php echo number_format($order["total_cost"]); ?> BDT</strong>
                </div>

                <!-- Error summary -->

                <?php if ($methodError != "" || $transactionError != "" || $paymentError != "") { ?>

                    <div class="alert">

                        <strong>Payment could not be completed.</strong>

                        <ul>
                            <?php if ($paymentError != "")     { ?><li><?php echo htmlspecialchars($paymentError);     ?></li><?php } ?>
                            <?php if ($methodError != "")      { ?><li><?php echo htmlspecialchars($methodError);      ?></li><?php } ?>
                            <?php if ($transactionError != "") { ?><li><?php echo htmlspecialchars($transactionError); ?></li><?php } ?>
                        </ul>

                    </div>

                <?php } ?>

                <!-- ========== PAYMENT FORM ========== -->

                <form class="payment-form" method="post" action="" id="paymentForm"
                      onsubmit="return validatePaymentForm();" novalidate>

                    <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
                    <input type="hidden" name="order_id" value="<?php echo $order["id"]; ?>">

                    <div class="form-group<?php if ($methodError != "") { echo " invalid"; } ?>">

                        <label>Choose a payment method</label>

                        <div class="pay-options">

                            <!--
                                One radio button per entry in the
                                $methodLabels array from the controller.
                                Building them in a loop means the screen
                                and the validation can never drift apart -
                                they read from the same list.

                                $value is the array key ("bkash"),
                                $text[0] is the label, $text[1] the hint.
                            -->

                            <?php foreach ($methodLabels as $value => $text) { ?>

                                <label class="pay-option">

                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="<?php echo htmlspecialchars($value); ?>"
                                        <?php if ($paymentMethod == $value) { echo "checked"; } ?>
                                    >

                                    <span class="pay-text">
                                        <span class="pay-name"><?php echo htmlspecialchars($text[0]); ?></span>
                                        <span class="pay-hint"><?php echo htmlspecialchars($text[1]); ?></span>
                                    </span>

                                </label>

                            <?php } ?>

                        </div>

                        <span class="error" id="methodError"><?php echo htmlspecialchars($methodError); ?></span>

                    </div>

                    <div class="form-group<?php if ($transactionError != "") { echo " invalid"; } ?>">

                        <label for="transaction_id">Transaction / reference number</label>

                        <input
                            type="text"
                            id="transaction_id"
                            name="transaction_id"
                            class="field-input"
                            placeholder="e.g. TRX8842190"
                            value="<?php echo htmlspecialchars($transactionId); ?>"
                        >

                        <span class="error" id="transactionError"><?php echo htmlspecialchars($transactionError); ?></span>

                    </div>

                    <button type="submit" class="primary-button">Confirm payment</button>

                    <p class="note">
                        No card numbers are stored. Only the method and your
                        reference number are kept against this order.
                    </p>

                </form>

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
