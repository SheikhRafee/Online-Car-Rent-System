<?php
/* =====================================================================
   controllers/history_control.php
   ---------------------------------------------------------------------
   The brain behind views/order_history.php.

   It is a short one, because the page only reads and prints.

   EXECUTION FLOW
       1.  Members only.
       2.  Fetch every order belonging to the user whose id is in the
           SESSION.

   There is no id in the URL anywhere on this page. The list is built
   purely from $_SESSION["user_id"], so there is nothing for a visitor
   to tamper with - you can only ever see your own bookings.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 */
requireMember();

$userId = $_SESSION["user_id"];


/* STEP 2 */
$db   = new mydb();
$conn = $db->openConn();

$orders = $db->findOrdersForUser($conn, $userId);

$conn->close();
