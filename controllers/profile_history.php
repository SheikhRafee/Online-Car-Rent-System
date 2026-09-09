<?php
/* =====================================================================
   controllers/profile_history.php
   ---------------------------------------------------------------------
   A small companion to profile_control.php.

   The profile page has to show the member's rental history, but
   profile_control.php belongs to Task 1 and knows nothing about orders.
   Rather than editing somebody else's file, this one adds the one thing
   that is missing: an array called $myOrders.

   views/profile.php includes profile_control.php first and this second,
   so by the time it starts printing, both are ready.

   EXECUTION FLOW
       1.  Only members rent cars, so an admin gets an empty list and
           the view hides the whole box.
       2.  Fetch every order belonging to the user in the SESSION.

   There is no id in the URL here. The list is built purely from
   $_SESSION["user_id"], so there is nothing for a visitor to tamper
   with - you can only ever see your own bookings.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


$myOrders = array();


/* profile_control.php has already run requireLogin(), so we know
   somebody is logged in and $_SESSION["user_id"] is safe to read. */
if (isLoggedIn() && $_SESSION["role"] == "member") {

    $db   = new mydb();
    $conn = $db->openConn();

    $myOrders = $db->findOrdersForUser($conn, $_SESSION["user_id"]);

    $conn->close();
}
