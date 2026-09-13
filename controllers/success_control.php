<?php
/* =====================================================================
   controllers/success_control.php
   ---------------------------------------------------------------------
   The brain behind views/success.php - the receipt shown after paying.

   EXECUTION FLOW
       1.  Members only.
       2.  Read the order number from the URL.
       3.  Fetch the order, filtered by the session's user id.
       4.  Refuse unless it really is 'confirmed'. Without this check,
           somebody could type success.php?id=5 for a CANCELLED order
           and the page would happily say "Booking confirmed".
       5.  Fetch the payment row, so we can show the reference number.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 */
requireMember();

$userId = $_SESSION["user_id"];


/* STEP 2 */
$orderId = 0;

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $orderId = (int) $_GET["id"];
}

if ($orderId < 1) {
    echo "<h1>Bad order number</h1>";
    echo "<p><a href='homepage.php'>Back to the cars</a></p>";
    exit;
}


/* STEP 3 */
$db   = new mydb();
$conn = $db->openConn();

$order = $db->findOrderForUser($conn, $orderId, $userId);

if ($order == null) {
    $conn->close();
    echo "<h1>Order not found</h1>";
    echo "<p>It does not exist, or it is not yours.</p>";
    echo "<p><a href='order_history.php'>See your orders</a></p>";
    exit;
}


/* STEP 4 - this page is only ever the receipt for a PAID order. */
if ($order["status"] != "confirmed") {
    $conn->close();
    echo "<h1>This order has not been paid for</h1>";
    echo "<p>Its status is currently " . htmlspecialchars($order["status"]) . ".</p>";
    echo "<p><a href='order_history.php'>See your orders</a></p>";
    exit;
}


/* STEP 5 */
$payment = $db->findPaymentForOrder($conn, $orderId);

$conn->close();

$days = countDays($order["start_date"], $order["end_date"]);

// The reference number, or a dash if for some reason there is no
// payment row (an order confirmed by hand in phpMyAdmin, say).
$reference = "-";

if ($payment != null) {
    $reference = $payment["transaction_id"];
}
