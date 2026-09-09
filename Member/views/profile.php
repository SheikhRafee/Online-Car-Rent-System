<?php
/* =====================================================================
   views/profile.php
   ---------------------------------------------------------------------
   Three boxes on one page:

       1. Profile details  - name, email, address, phone, picture
       2. Change password  - current, new, confirm
       3. Rental history   - every order this member has placed

   Boxes 1 and 2 are two SEPARATE forms that both post back to this
   same page. controllers/profile_control.php tells them apart by the NAME
   of the submit button that arrived:

       <input type="submit" name="update_profile" ...>
       <input type="submit" name="change_password" ...>

   so isset($_POST["update_profile"]) is true only for the first one.
   ===================================================================== */

require_once __DIR__ . "/../controllers/profile_control.php";

/* profile_control.php is Task 1's file and knows nothing about orders,
   so this second small controller fetches the rental history for the
   box at the bottom of the page. */
require_once __DIR__ . "/../controllers/profile_history.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile &mdash; Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css">

</head>

<body class="homepage">

    <!-- ================= HEADER ================= -->

    <header class="homepage-header">

        <div class="logo">
            Car<span>Rental</span>
        </div>

        <nav>
            <a href="homepage.php">Home</a>
            <?php if (file_exists("cars.php")) { ?>
                <a href="cars.php">Cars</a>
            <?php } ?>
            <a href="order_history.php">Order History</a>
            <?php if (file_exists("blog.php")) { ?>
                <a href="blog.php">Blog</a>
            <?php } ?>
            <a href="profile.php" class="active">Profile</a>
            <a href="../controllers/login_control.php?logout=true">Logout</a>
        </nav>

    </header>

    <!-- ================= MAIN ================= -->

    <main class="profile-main">

        <div class="page-title">
            <div>
                <h1>Your Profile</h1>
                <p>Update your details or change your password.</p>
            </div>
        </div>

        <!-- ============ BOX 1 : PROFILE DETAILS ============ -->

        <div class="form-box">

            <h2>Profile Details</h2>

            <?php if ($profileSuccess != "") { ?>
                <p class="form-success"><?php echo htmlspecialchars($profileSuccess); ?></p>
            <?php } ?>

            <?php if ($profileError != "") { ?>
                <span class="error"><?php echo htmlspecialchars($profileError); ?></span>
            <?php } ?>

            <!--
                enctype="multipart/form-data" is REQUIRED on any form
                that uploads a file. Without it the browser sends only
                the file's name as text, and $_FILES arrives empty.
            -->
            <form action="" method="post" id="profileForm"
                  onsubmit="return validateProfileForm();"
                  enctype="multipart/form-data" novalidate>

                <div class="profile-picture-row">

                    <?php
                    $pictureFile = "../public/uploads/profiles/" . $userRow["profile_picture"];
                    ?>

                    <?php if ($userRow["profile_picture"] != "" && file_exists($pictureFile)) { ?>

                        <img src="<?php echo htmlspecialchars($pictureFile); ?>" alt="Profile picture">

                    <?php } else { ?>

                        <!-- No picture yet, so show the first letter of
                             the name in a grey circle instead.
                             substr($text, 0, 1) takes 1 character
                             starting at position 0. -->
                        <div class="no-picture">
                            <?php echo htmlspecialchars(strtoupper(substr($userRow["name"], 0, 1))); ?>
                        </div>

                    <?php } ?>

                    <div>
                        <label for="profile_picture">Profile picture (JPEG/PNG, max 2MB)</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png">
                        <span class="error" id="pictureError"><?php echo htmlspecialchars($pictureError); ?></span>
                    </div>

                </div>

                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <span class="error" id="nameError"><?php echo htmlspecialchars($nameError); ?></span>

                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error" id="emailError"><?php echo htmlspecialchars($emailError); ?></span>

                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
                <span class="error" id="addressError"><?php echo htmlspecialchars($addressError); ?></span>

                <label for="phone">Phone (11 digits)</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                <span class="error" id="phoneError"><?php echo htmlspecialchars($phoneError); ?></span>

                <input type="submit" name="update_profile" value="Save Changes">

            </form>

        </div>

        <!-- ============ BOX 2 : CHANGE PASSWORD ============ -->

        <div class="form-box">

            <h2>Change Password</h2>

            <?php if ($passwordSuccess != "") { ?>
                <p class="form-success"><?php echo htmlspecialchars($passwordSuccess); ?></p>
            <?php } ?>

            <form action="" method="post" id="passwordForm"
                  onsubmit="return validatePasswordForm();" novalidate>

                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
                <span class="error" id="currentPasswordError"><?php echo htmlspecialchars($currentPasswordError); ?></span>

                <label for="new_password">New Password (at least 8 characters)</label>
                <input type="password" id="new_password" name="new_password">
                <span class="error" id="newPasswordError"><?php echo htmlspecialchars($newPasswordError); ?></span>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <span class="error" id="confirmPasswordError"><?php echo htmlspecialchars($confirmPasswordError); ?></span>

                <input type="submit" name="change_password" value="Change Password">

            </form>

        </div>

        <!-- ============ BOX 3 : RENTAL HISTORY ============ -->
        <!--
            Only members rent cars, so an admin does not need this box.
        -->

        <?php if ($currentUser["role"] == "member") { ?>

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

                                <?php foreach ($myOrders as $oneOrder) { ?>

                                    <tr>
                                        <td data-label="Order">#<?php echo $oneOrder["id"]; ?></td>

                                        <td data-label="Car"><?php echo htmlspecialchars($oneOrder["car_name"]); ?></td>

                                        <td data-label="Collection"><?php echo date("d M Y", strtotime($oneOrder["start_date"])); ?></td>

                                        <td data-label="Return"><?php echo date("d M Y", strtotime($oneOrder["end_date"])); ?></td>

                                        <td data-label="Total"><?php echo number_format($oneOrder["total_cost"]); ?> BDT</td>

                                        <td data-label="Status">
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

                    <p class="no-results">You have not rented a car yet.</p>

                <?php } ?>

            </div>

        <?php } ?>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

    <script src="../public/js/validation.js"></script>

</body>

</html>
