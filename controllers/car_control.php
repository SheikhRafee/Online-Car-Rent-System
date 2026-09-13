<?php
/* =====================================================================
   controllers/car_control.php
   ---------------------------------------------------------------------
   The brain behind views/car_details.php.

   This page does two different jobs depending on how you arrive:

       Arriving by LINK (a GET)   -> just show the car and an empty
                                     booking form.
       Pressing "Place order"     -> a POST comes back to the same
                                     page, and this file validates the
                                     dates and creates the order.

   EXECUTION FLOW
       1.  Must be logged in to look at a car.
       2.  Work out which car: from the URL on a normal visit, from a
           hidden field when the form comes back.
       3.  Load that car. Stop if there is no such car.
       4.  If this is not a POST, we are done - show the form.
       5.  Members only from here on (an admin cannot rent a car).
       6.  Read the two dates.
       7.  Validate them: real dates, not in the past, in the right
           order, not longer than 30 days.
       8.  Check nobody else has already booked the car for those days.
       9.  Work out the total: days x the price FROM THE DATABASE.
      10.  Save the order and send the member to the invoice.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* STEP 1 */
requireLogin();

$userId = $_SESSION["user_id"];


/* -----------------------------------------------------------------
   STEP 2 - which car?

   On a normal visit the id is in the URL   (car_details.php?id=2).
   When the form is submitted it comes back in a hidden field, because
   a POST does not carry the old URL's questions with it.

   Either way we treat it as untrusted text: is_numeric() first, then
   (int) to force it into a whole number.
   ----------------------------------------------------------------- */
$carId = 0;

if (isset($_POST["car_id"]) && is_numeric($_POST["car_id"])) {

    $carId = (int) $_POST["car_id"];

} else if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

    $carId = (int) $_GET["id"];
}

if ($carId < 1) {
    echo "<h1>Bad car number</h1>";
    echo "<p><a href='homepage.php'>Back to the cars</a></p>";
    exit;
}


/* STEP 3 - load the car. */
$db   = new mydb();
$conn = $db->openConn();

$car = $db->findCarById($conn, $carId);

if ($car == null) {
    $conn->close();
    echo "<h1>Car not found</h1>";
    echo "<p><a href='homepage.php'>Back to the cars</a></p>";
    exit;
}


/* -----------------------------------------------------------------
   Values the view will need. They start empty so the form can be
   drawn on a first visit.

   THE PRICE ALWAYS COMES FROM THE DATABASE ROW, never from the form.
   If we let the browser send us a price, anyone could open dev tools,
   change it to 1, and rent a car for one taka.
   ----------------------------------------------------------------- */
$pricePerDay = $car["price_per_day"];

$startDate = "";
$endDate   = "";
$days      = 0;
$total     = 0;

$startDateError = "";
$endDateError   = "";
$orderError     = "";


/* STEP 4 - nothing else to do unless the form was submitted. */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* STEP 5 - only members can place an order. */
    requireMember();

    checkToken();


    /* STEP 6 - read the dates. */
    if (isset($_POST["start_date"])) { $startDate = trim($_POST["start_date"]); }
    if (isset($_POST["end_date"]))   { $endDate   = trim($_POST["end_date"]);   }


    /* -------------------------------------------------------------
       STEP 7 - validate.

       Note the order of the checks on each date: empty first, then
       "is it real", then "is it sensible". Each  else if  only runs
       when the one before it passed, so we never test a date we
       already know is rubbish.
       ------------------------------------------------------------- */

    // Has the car gone out of service since the page was drawn?
    if ($car["availability_status"] != "available") {
        $orderError = "This car is no longer available.";
    }

    // --- collection date ---
    if ($startDate == "") {

        $startDateError = "Please choose a collection date.";

    } else if (!isRealDate($startDate)) {

        $startDateError = "That is not a real date.";

    } else if ($startDate < date("Y-m-d")) {

        /*
           Both sides here are text in the form "2026-09-15".
           Because the year comes first, then the month, then the day,
           comparing them alphabetically gives the same answer as
           comparing them as dates - which is why this simple < works.
        */
        $startDateError = "The collection date cannot be in the past.";
    }

    // --- return date ---
    if ($endDate == "") {

        $endDateError = "Please choose a return date.";

    } else if (!isRealDate($endDate)) {

        $endDateError = "That is not a real date.";
    }

    // --- the two dates together ---
    // Only worth checking once we know both dates are real.
    if ($startDateError == "" && $endDateError == "") {

        $days = countDays($startDate, $endDate);

        if ($days < 1) {
            $endDateError = "The return date must be after the collection date.";
        } else if ($days > 30) {
            $endDateError = "A rental cannot be longer than 30 days.";
        }
    }


    /* -------------------------------------------------------------
       STEP 8 - is the car free on those days?

       Without this check, two members could both book the same car
       for the same week and neither would ever know.
       ------------------------------------------------------------- */
    if ($startDateError == "" && $endDateError == "" && $orderError == "") {

        if ($db->isCarBooked($conn, $carId, $startDate, $endDate)) {
            $orderError = "Sorry, this car is already booked for some of those dates.";
        }
    }


    /* -------------------------------------------------------------
       STEP 9 & 10 - all clear, so save the order.
       ------------------------------------------------------------- */
    if ($startDateError == "" && $endDateError == "" && $orderError == "") {

        $total = $days * $pricePerDay;

        $orderId = $db->insertOrder($conn, $userId, $carId, $startDate, $endDate, $total);

        $conn->close();

        if ($orderId > 0) {
            /*
               Redirect after a successful POST.
               If we simply printed the invoice here, pressing F5 would
               re-send the form and book the same car a second time.
               Sending the browser off to a fresh page makes refreshing
               completely safe.
            */
            header("Location: " . BASE_URL . "/views/invoice.php?id=" . $orderId);
            exit;
        }

        // The insert failed. Show a normal message rather than a crash.
        echo "<h1>Your order could not be saved</h1>";
        echo "<p><a href='car_details.php?id=" . $carId . "'>Try again</a></p>";
        exit;
    }
}


/* If we reach here the page is about to be drawn, so we are finished
   with the database. */
$conn->close();
