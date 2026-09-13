<?php
/* =====================================================================
   views/rental_history.php          (Task 3)
   ---------------------------------------------------------------------
   The "Rental History" box that belongs on the member's profile page.

   WHY THIS IS ITS OWN FILE
       The profile page itself is Task 1's work. Task 3 only has to add
       ONE thing to it - a table of the member's past bookings. Rather
       than editing somebody else's page (and creating a merge conflict
       over it), that one box lives here, in a file Task 3 owns.

   HOW TO PUT IT ON THE PROFILE PAGE
       Two lines. At the TOP of views/profile.php, next to whatever
       controller it already includes:

           require_once __DIR__ . "/../controllers/profile_history.php";

       and then, wherever the box should appear on the page:

           include __DIR__ . "/rental_history.php";

       That is the whole integration. Nothing else in profile.php
       changes.

   WHAT IT NEEDS
       $myOrders     - set by controllers/profile_history.php
       $currentUser  - already set by the profile page's own controller

   The isset() guards below mean that if either one is missing the box
   simply does not draw, instead of crashing the page with a PHP error.
   ===================================================================== */
?>

<?php
/* Only members rent cars, so an admin has no history to show. */
$showRentalHistory = false;

if (isset($currentUser) && isset($myOrders)) {
    if ($currentUser["role"] == "member") {
        $showRentalHistory = true;
    }
}
?>

<?php if ($showRentalHistory) { ?>

    <div class="form-box">

        <h2>Rental History</h2>

        <?php if (count($myOrders) > 0) { ?>

            <div class="table-scroll">

                <table class="history-table">

                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Car</th>
                            <th>Collection</th>
                            <th>Return</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!--
                            One row per booking. foreach walks through the
                            $myOrders array and hands us one order at a time.
                        -->

                        <?php foreach ($myOrders as $oneOrder) { ?>

                            <tr>
                                <td data-label="Order">#<?php echo $oneOrder["id"]; ?></td>

                                <td data-label="Car"><?php echo htmlspecialchars($oneOrder["car_name"]); ?></td>

                                <!-- The database stores "2026-09-15". strtotime turns
                                     that into a number and date() prints it in a
                                     friendlier shape. -->
                                <td data-label="Collection"><?php echo date("d M Y", strtotime($oneOrder["start_date"])); ?></td>

                                <td data-label="Return"><?php echo date("d M Y", strtotime($oneOrder["end_date"])); ?></td>

                                <td data-label="Total"><?php echo number_format($oneOrder["total_cost"]); ?> BDT</td>

                                <td data-label="Status">
                                    <!-- The status doubles as a CSS class, so
                                         "confirmed" is green and "cancelled" grey. -->
                                    <span class="badge badge-<?php echo htmlspecialchars($oneOrder["status"]); ?>">
                                        <?php echo htmlspecialchars($oneOrder["status"]); ?>
                                    </span>
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        <?php } else { ?>

            <!-- Shown to a member who has never booked anything -->
            <p class="no-results">You have not rented a car yet.</p>

        <?php } ?>

    </div>

<?php } ?>
