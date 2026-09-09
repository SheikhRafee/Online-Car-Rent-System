<?php
/* =====================================================================
   controllers/ajax_cost.php
   ---------------------------------------------------------------------
   THE AJAX ENDPOINT.

   The car details page uses this to show the number of days and the
   total cost the moment the member picks their two dates - without
   reloading the page.

   HOW AJAX WORKS HERE (viva answer)
       1.  The member changes a date box.
       2.  JavaScript (public/js/validation.js) quietly sends a request
           to this file in the background.
       3.  This file works out the answer and prints it as plain text.
       4.  JavaScript reads that text and writes the numbers into the
           little summary box. The page never reloads.

   THE ANSWER FORMAT
       We print ONE line, with the pieces separated by a | character:

           OK|3|12600                        <- 3 days, 12600 taka
           ERROR|That is not a real date.    <- something was wrong
           ERROR|                            <- nothing to say yet

       The first piece is always OK or ERROR, so JavaScript only has to
       look at that to know what it is holding. A | is used as the
       divider because it can never appear in a number or in any of our
       messages, so splitting on it is always safe.

   IMPORTANT: this file prints that one line and NOTHING else. Not one
   character of HTML, or the JavaScript on the other end would read the
   markup as part of the answer.

   The price used here still comes from the CARS TABLE, never from the
   browser. This page is only a convenience for the member's eyes - the
   real total is calculated again by car_control.php before saving.
   ===================================================================== */

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/helpers.php";
require_once __DIR__ . "/../models/session_helper.php";


/* -----------------------------------------------------------------
   Tell the browser "what follows is plain text, not a web page".
   This header must be sent before anything is printed.
   ----------------------------------------------------------------- */
header("Content-Type: text/plain");


/* -----------------------------------------------------------------
   Two small helpers, so every exit from this file looks the same.
   Both print one line and stop the script immediately.
   ----------------------------------------------------------------- */

function sendOk($days, $total)
{
    echo "OK|" . $days . "|" . $total;
    exit;
}

function sendError($message)
{
    echo "ERROR|" . $message;
    exit;
}


/* -----------------------------------------------------------------
   1. Members only.

   We cannot use requireMember() here, because that prints HTML and
   would end up inside our one line of text. So we do the same check
   by hand and answer in our own format instead.
   ----------------------------------------------------------------- */
if (!isLoggedIn() || $_SESSION["role"] != "member") {
    sendError("Please log in as a member.");
}


/* -----------------------------------------------------------------
   2. Read the three values JavaScript sent us.
   ----------------------------------------------------------------- */
$carId     = 0;
$startDate = "";
$endDate   = "";

if (isset($_GET["car_id"]) && is_numeric($_GET["car_id"])) {
    $carId = (int) $_GET["car_id"];
}

if (isset($_GET["start_date"])) { $startDate = trim($_GET["start_date"]); }
if (isset($_GET["end_date"]))   { $endDate   = trim($_GET["end_date"]);   }


/* -----------------------------------------------------------------
   3. Check them. Same rules as the real form.
   ----------------------------------------------------------------- */
if ($carId < 1) {
    sendError("No car chosen.");
}

if ($startDate == "" || $endDate == "") {
    sendError("");          // empty message - the member is still typing
}

if (!isRealDate($startDate) || !isRealDate($endDate)) {
    sendError("That is not a real date.");
}

// date("Y-m-d") is today, written the same way as the form's dates,
// so the two can be compared directly as text.
if ($startDate < date("Y-m-d")) {
    sendError("The collection date cannot be in the past.");
}

$days = countDays($startDate, $endDate);

if ($days < 1) {
    sendError("The return date must be after the collection date.");
}

if ($days > 30) {
    sendError("A rental cannot be longer than 30 days.");
}


/* -----------------------------------------------------------------
   4. Look the car up and do the sum.
   ----------------------------------------------------------------- */
$db   = new mydb();
$conn = $db->openConn();

$car = $db->findCarById($conn, $carId);

if ($car == null) {
    $conn->close();
    sendError("That car does not exist.");
}

if ($car["availability_status"] != "available") {
    $conn->close();
    sendError("This car is not available.");
}

// Warn early if somebody else already has the car on those dates.
if ($db->isCarBooked($conn, $carId, $startDate, $endDate)) {
    $conn->close();
    sendError("This car is already booked for some of those dates.");
}

$total = $days * $car["price_per_day"];

$conn->close();


/* 5. Send the good answer back. */
sendOk($days, $total);
