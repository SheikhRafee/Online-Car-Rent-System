<?php
/* =====================================================================
   controllers/home_control.php
   ---------------------------------------------------------------------
   The brain behind views/homepage.php.

   The home page is the first thing a member sees after logging in. It
   shows every available car, and lets them narrow the list down to one
   category.

   EXECUTION FLOW
       1.  Make sure somebody is logged in.
       2.  Ask the database which categories exist.
       3.  Add "All" to the front of that list by hand - "All" is not a
           real category sitting in the cars table, it is just our way
           of saying "do not filter".
       4.  Read the category from the URL (homepage.php?type=Microbus).
       5.  Check it is one of ours, and fall back to "All" if not.
       6.  Fetch the cars for that category.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 - guard the page.
   requireLogin() sends anyone without a session to the login page, and
   otherwise hands back that user's row - which is how the page greets
   them by name. */
$currentUser = requireLogin();


$db   = new mydb();
$conn = $db->openConn();


/* STEP 2 - the real categories, straight from the cars table. */
$carTypes = $db->getDistinctCarTypes($conn);


/* -----------------------------------------------------------------
   STEP 3 - build the list the page will show: "All" first, then the
   real categories.

   We start a new array with "All" already in it, then copy the real
   ones on the end. (This is easier to read than array_unshift.)
   ----------------------------------------------------------------- */
$categories = array("All");

foreach ($carTypes as $oneType) {
    $categories[] = $oneType;
}


/* -----------------------------------------------------------------
   STEP 4 & 5 - which category did the visitor ask for?

   Anything can be typed into the address bar, so we never pass the
   URL value straight to the database. in_array() asks "is this value
   somewhere in our list?" - if it is not, we quietly show everything.
   ----------------------------------------------------------------- */
$selectedCategory = "All";

if (isset($_GET["type"])) {

    $wanted = trim($_GET["type"]);

    if (in_array($wanted, $categories)) {
        $selectedCategory = $wanted;
    }
}


/* STEP 6 - the cars themselves. */
$cars = $db->getAvailableCars($conn, $selectedCategory);

$conn->close();
