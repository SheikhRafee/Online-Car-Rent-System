<?php
/* =====================================================================
   controllers/invoice_control.php
   ---------------------------------------------------------------------
   The brain behind views/invoice.php.

   The invoice is the "are you sure?" step. The order already exists in
   the database at this point, but its status is only 'pending' - so
   nothing is really booked and nothing has been paid.

   EXECUTION FLOW
       1.  Members only.
       2.  Read the order number out of the URL and check it.
       3.  Fetch the order - but only if it belongs to this member.
       4.  Work out how many days it covers, for the breakdown table.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 */
requireMember();

$userId = $_SESSION["user_id"];


/* -----------------------------------------------------------------
   STEP 2 - which order? It arrives as invoice.php?id=12
   ----------------------------------------------------------------- */
$orderId = 0;

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $orderId = (int) $_GET["id"];
}

if ($orderId < 1) {
    echo "<h1>Bad order number</h1>";
    echo "<p><a href='homepage.php'>Back to the cars</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   STEP 3 - fetch it.

   This is the important security line on this page. The lookup filters
   on the order id AND on the user id from the SESSION. So if you edit
   the URL to invoice.php?id=1 and order 1 belongs to somebody else,
   the query finds nothing and you get "Order not found" - you can
   never read another member's invoice by guessing numbers.
   ----------------------------------------------------------------- */
$db   = new mydb();
$conn = $db->openConn();

$order = $db->findOrderForUser($conn, $orderId, $userId);

$conn->close();

if ($order == null) {
    echo "<h1>Order not found</h1>";
    echo "<p>It does not exist, or it is not yours.</p>";
    echo "<p><a href='order_history.php'>See your orders</a></p>";
    exit;
}


/* STEP 4 - the rental length, for the cost breakdown. */
$days = countDays($order["start_date"], $order["end_date"]);
