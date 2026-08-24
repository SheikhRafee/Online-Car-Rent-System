<?php
require_once "../controllers/profile_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile - Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?php echo @filemtime(__DIR__ . "/../public/css/style.css"); ?>">

</head>

<body class="homepage">

    <!-- ================= HEADER ================= -->

    <header class="homepage-header">

        <div class="logo">
            Car<span>Rental</span>
        </div>

        <nav>

            <a href="homepage.php">Home</a>

            <a href="cars.php">Cars</a>

            <a href="order_history.php">Order History</a>

            <a href="blog.php">Blog</a>

            <a href="profile.php" class="active">Profile</a>

            <a href="../controllers/login_control.php?logout=true">
                Logout
            </a>

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

        <!-- ========== PROFILE DETAILS ========== -->

        <div class="form-box">

            <h2>Profile Details</h2>

            <?php if ($profileSuccess !== "") { ?>
                <p class="form-success"><?php echo htmlspecialchars($profileSuccess); ?></p>
            <?php } ?>

            <?php if ($profileError !== "") { ?>
                <span class="error"><?php echo htmlspecialchars($profileError); ?></span>
            <?php } ?>

            <form
                action=""
                method="post"
                id="profileForm"
                onsubmit="return validateProfileForm();"
                enctype="multipart/form-data"
                novalidate
            >

                <div class="profile-picture-row">

                    <?php if (!empty($userRow["profile_picture"])) { ?>

                        <img
                            src="../public/uploads/profiles/<?php echo htmlspecialchars($userRow["profile_picture"]); ?>"
                            alt="Profile picture"
                        >

                    <?php } else { ?>

                        <div class="no-picture">
                            <?php echo htmlspecialchars(strtoupper(substr($userRow["name"], 0, 1))); ?>
                        </div>

                    <?php } ?>

                    <div>
                        <label for="profile_picture">Profile picture (JPEG/PNG, max 2MB)</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png">
                        <span class="error" id="pictureError"><?php echo $pictureError; ?></span>
                    </div>

                </div>

                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <span class="error" id="nameError"><?php echo $nameError; ?></span>

                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error" id="emailError"><?php echo $emailError; ?></span>

                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
                <span class="error" id="addressError"><?php echo $addressError; ?></span>

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                <span class="error" id="phoneError"><?php echo $phoneError; ?></span>

                <input type="submit" name="update_profile" value="Save Changes">

            </form>

        </div>

        <!-- ========== CHANGE PASSWORD ========== -->

        <div class="form-box">

            <h2>Change Password</h2>

            <?php if ($passwordSuccess !== "") { ?>
                <p class="form-success"><?php echo htmlspecialchars($passwordSuccess); ?></p>
            <?php } ?>

            <form
                action=""
                method="post"
                id="passwordForm"
                onsubmit="return validatePasswordForm();"
                novalidate
            >

                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
                <span class="error" id="currentPasswordError"><?php echo $currentPasswordError; ?></span>

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
                <span class="error" id="newPasswordError"><?php echo $newPasswordError; ?></span>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <span class="error" id="confirmPasswordError"><?php echo $confirmPasswordError; ?></span>

                <input type="submit" name="change_password" value="Change Password">

            </form>

        </div>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">

        <p>
            &copy; 2026 Car Rental System. All Rights Reserved.
        </p>

    </footer>

    <script src="../public/js/validation.js"></script>

</body>

</html>
