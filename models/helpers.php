<?php
/* =====================================================================
   Model/helpers.php
   ---------------------------------------------------------------------
   Two small date functions that more than one page needs.
   They are kept here so the same logic is not copy-pasted around.
   ===================================================================== */


/* ---------------------------------------------------------------------
   Is this text a date that really exists?

   An <input type="date"> gives us text like "2026-09-15".
   A user can also send anything they like using dev tools, and even a
   normal-looking date can be fake: "2026-02-31" reads fine, but
   February has no 31st.

   How it works:
       explode("-", $text) cuts the text at every dash and gives us
       array("2026", "09", "15").
       checkdate() is a built-in PHP function that answers
       "is month/day/year a real calendar date?"
   --------------------------------------------------------------------- */
function isRealDate($text)
{
    $parts = explode("-", $text);

    // We need exactly three pieces: year, month, day.
    if (count($parts) != 3) {
        return false;
    }

    $year  = (int) $parts[0];    // (int) turns "2026" into the number 2026
    $month = (int) $parts[1];
    $day   = (int) $parts[2];

    return checkdate($month, $day, $year);
}


/* ---------------------------------------------------------------------
   How many days between two dates?

   strtotime() turns "2026-09-15" into a plain number: how many seconds
   have passed since 1 January 1970. Subtracting the two numbers gives
   the seconds in between, and there are 86400 seconds in a day
   (60 seconds x 60 minutes x 24 hours = 86400).
   --------------------------------------------------------------------- */
function countDays($startDate, $endDate)
{
    $start = strtotime($startDate);
    $end   = strtotime($endDate);

    $seconds = $end - $start;

    return $seconds / 86400;
}


/* ---------------------------------------------------------------------
   Turn the value stored in the database into something readable.

   MySQL holds "cash_on_delivery" because that is what the ENUM column
   accepts. Nobody wants to read that on a receipt, so we translate.

   The order has no payment method until it is paid for, so a NULL or
   an unknown value gives us a dash.
   --------------------------------------------------------------------- */
function paymentMethodName($key)
{
    $names = array(
        "credit_card"      => "Credit card",
        "bkash"            => "bKash",
        "nagad"            => "Nagad",
        "bank_transfer"    => "Bank transfer",
        "cash_on_delivery" => "Cash on delivery"
    );

    // array_key_exists() asks "is this key in the list?"
    if ($key != null && array_key_exists($key, $names)) {
        return $names[$key];
    }

    return "-";
}
