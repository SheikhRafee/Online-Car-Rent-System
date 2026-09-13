<?php
/* =====================================================================
   controllers/payment_control.php
   ---------------------------------------------------------------------
   The brain behind views/payment.php.

   EXECUTION FLOW
       1.  Members only.
       2.  Work out which order (URL on arrival, hidden field on POST).
       3.  Fetch it - and only if it belongs to this member.
       4.  Refuse if it is not still 'pending'. Paying twice is not
           a thing.
       5.  If this is not a POST, stop - just draw the payment form.
       6.  Read and validate the payment method and the reference.
       7.  Mark the order confirmed, then save the payment row.
       8.  Redirect to the success page.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 */
requireMember();

$userId = $_SESSION["user_id"];


/* STEP 2 - which order? */
$orderId = 0;

if (isset($_POST["order_id"]) && is_numeric($_POST["order_id"])) {

    $orderId = (int) $_POST["order_id"];

} else if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

    $orderId = (int) $_GET["id"];
}

if ($orderId < 1) {
    echo "<h1>Bad order number</h1>";
    echo "<p><a href='homepage.php'>Back to the cars</a></p>";
    exit;
}


/* STEP 3 - fetch it, filtered by the session's user id. */
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


/* -----------------------------------------------------------------
   STEP 4 - an order can only be paid for while it is 'pending'.

   This is what stops a member pressing Back after paying and paying
   for the same booking twice.
   ----------------------------------------------------------------- */
if ($order["status"] != "pending") {
    $conn->close();
    echo "<h1>This order is already " . htmlspecialchars($order["status"]) . "</h1>";
    echo "<p><a href='order_history.php'>See your orders</a></p>";
    exit;
}


$days = countDays($order["start_date"], $order["end_date"]);


/* -----------------------------------------------------------------
   The five payment methods. This list is the whole point of the
   validation below: the payment_method column in MySQL is an ENUM
   that accepts exactly these five words and nothing else, so we
   check against them BEFORE we try to save.

   The key is what goes into the database.
   The two texts are the label and the small grey hint on screen.
   ----------------------------------------------------------------- */
$methodLabels = array(
    "bkash"            => array("bKash",            "Mobile wallet"),
    "nagad"            => array("Nagad",            "Mobile wallet"),
    "credit_card"      => array("Credit card",      "Visa or Mastercard"),
    "bank_transfer"    => array("Bank transfer",    "Direct deposit"),
    "cash_on_delivery" => array("Cash on delivery", "Pay when the car arrives")
);


$paymentMethod = "";
$transactionId = "";

$methodError      = "";
$transactionError = "";
$paymentError     = "";


/* STEP 5 - only work when the form was submitted. */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    checkToken();


    /* STEP 6 - read and check. */
    if (isset($_POST["payment_method"])) { $paymentMethod = $_POST["payment_method"];        }
    if (isset($_POST["transaction_id"])) { $transactionId = trim($_POST["transaction_id"]); }

    /*
       A radio group is not a guarantee either. Dev tools can change a
       radio's value to anything before the form is sent, so we check
       the value we received is really one of our five.

       array_key_exists() looks for the key in $methodLabels, which is
       the same list we drew the radio buttons from.
    */
    if ($paymentMethod == "") {
        $methodError = "Please choose a payment method.";
    } else if (!array_key_exists($paymentMethod, $methodLabels)) {
        $methodError = "That is not a payment method we accept.";
    }

    // ctype_alnum() is true only when every character is a letter or
    // a digit - no spaces, no symbols, nothing that could confuse us.
    if ($transactionId == "") {
        $transactionError = "Please enter your transaction or reference number.";
    } else if (strlen($transactionId) < 4) {
        $transactionError = "That reference looks too short.";
    } else if (strlen($transactionId) > 100) {
        $transactionError = "That reference is too long.";
    } else if (!ctype_alnum($transactionId)) {
        $transactionError = "Use letters and numbers only.";
    }


    /* -------------------------------------------------------------
       STEP 7 - save.

       THE AMOUNT COMES FROM $order["total_cost"], which we read out
       of the orders table a moment ago. Nothing about the price is
       taken from the form, so the browser cannot decide what it paid.

       Order of the two writes matters:

         a) confirmOrder() runs FIRST. Its SQL only touches a row that
            is still 'pending', and it reports how many rows changed.
            So if the member somehow got here twice, the second attempt
            changes nothing and returns false - and we stop before
            writing a second payment row.

         b) Only when that worked do we save the payment record.

       (In a bigger system you would wrap both writes in a database
       transaction so they succeed or fail as one unit. Here the guard
       inside confirmOrder does the same job in a simpler way.)
       ------------------------------------------------------------- */
    if ($methodError == "" && $transactionError == "") {

        $amount = $order["total_cost"];

        if ($db->confirmOrder($conn, $orderId, $userId, $paymentMethod)) {

            $db->insertPayment($conn, $orderId, $amount, $paymentMethod, $transactionId);

            $conn->close();

            /*
               STEP 8 - redirect after a successful POST, so that
               refreshing the success page cannot pay again.
            */
            header("Location: " . BASE_URL . "/views/success.php?id=" . $orderId);
            exit;

        } else {

            $paymentError = "This order is no longer waiting for payment.";
        }
    }
}


$conn->close();
