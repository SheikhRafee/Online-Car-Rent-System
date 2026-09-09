<?php
/* =====================================================================
   controllers/order_control.php
   ---------------------------------------------------------------------
   Handles the two buttons at the bottom of the invoice page:

       "Cancel order"    -> set the order's status to 'cancelled'
       "Finalise & pay"  -> send the member on to the payment page

   This is a pure controller: it prints no page of its own. Every path
   through it ends in either a redirect or a short error message, and
   every path ends with  exit .

   EXECUTION FLOW
       1.  Only a member can be here.
       2.  Refuse anything that is not a POST.
       3.  Check the CSRF token.
       4.  Check the action is one we recognise.
       5.  Check the order id is a sensible number.
       6.  Check the order exists AND belongs to this member.
       7.  Check it is still 'pending'.
       8.  Do the thing, then redirect.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 - members only. */
requireMember();

$userId = $_SESSION["user_id"];


/* -----------------------------------------------------------------
   STEP 2 - anything that CHANGES data must arrive by POST.

   When you type a URL into the address bar the browser sends a GET.
   So this check turns away anyone who tries to cancel an order just
   by pasting order_control.php into the address bar. It also means
   search engines and browser prefetching can never cancel an order
   by accident, because they only ever send GETs.
   ----------------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "<h1>Not allowed</h1>";
    echo "<p>This page can only be reached by pressing a button.</p>";
    echo "<p><a href='../views/homepage.php'>Back to the cars</a></p>";
    exit;
}


/* STEP 3 - CSRF token. */
checkToken();


/* -----------------------------------------------------------------
   STEP 4 - which button was pressed?

   The value comes from a hidden field, and a hidden field is not a
   promise - the browser can send anything at all. So we compare it
   against the only two values we accept and reject everything else.
   ----------------------------------------------------------------- */
$action = "";

if (isset($_POST["action"])) {
    $action = $_POST["action"];
}

if ($action != "cancel" && $action != "finalize") {
    echo "<h1>Unknown action</h1>";
    echo "<p><a href='../views/homepage.php'>Back to the cars</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   STEP 5 - which order?

   is_numeric() checks the text is a number at all, then (int) turns
   it into a real integer. Doing both means "7" works, but "abc" and
   "7 OR 1=1" are stopped right here.
   ----------------------------------------------------------------- */
$orderId = 0;

if (isset($_POST["order_id"]) && is_numeric($_POST["order_id"])) {
    $orderId = (int) $_POST["order_id"];
}

if ($orderId < 1) {
    echo "<h1>Bad order number</h1>";
    echo "<p><a href='../views/homepage.php'>Back to the cars</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   STEP 6 - does the order exist, and is it this member's?

   findOrderForUser() filters on BOTH the order id and the session's
   user id, so somebody else's order comes back as null - exactly the
   same as an order that does not exist. We do not tell the visitor
   which of the two it was.
   ----------------------------------------------------------------- */
$db   = new mydb();
$conn = $db->openConn();

$order = $db->findOrderForUser($conn, $orderId, $userId);

if ($order == null) {
    $conn->close();
    echo "<h1>Order not found</h1>";
    echo "<p>It does not exist, or it is not yours.</p>";
    echo "<p><a href='../views/order_history.php'>See your orders</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   STEP 7 - is it still open?

   An order that is already confirmed or already cancelled must not be
   acted on a second time.
   ----------------------------------------------------------------- */
if ($order["status"] != "pending") {
    $conn->close();
    echo "<h1>This order is already " . htmlspecialchars($order["status"]) . "</h1>";
    echo "<p><a href='../views/order_history.php'>See your orders</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   STEP 8 - do it.
   ----------------------------------------------------------------- */
if ($action == "cancel") {

    $ok = $db->updateOrderStatus($conn, $orderId, $userId, "cancelled");

    $conn->close();

    if (!$ok) {
        echo "<h1>The order could not be cancelled</h1>";
        echo "<p><a href='../views/order_history.php'>See your orders</a></p>";
        exit;
    }

    header("Location: " . BASE_URL . "/views/order_history.php");
    exit;
}


/* The other case: action == "finalize".
   Nothing is written to the database yet - the member still has to
   choose how to pay - so we only forward them to the payment page. */
$conn->close();

header("Location: " . BASE_URL . "/views/payment.php?id=" . $orderId);
exit;
