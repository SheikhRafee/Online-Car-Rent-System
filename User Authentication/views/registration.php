<?php
require_once "../controllers/reg_control.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?php echo @filemtime(__DIR__ . "/../public/css/style.css"); ?>">
</head>
<body>

    <div class="form-box">

        <h2>Create an Account</h2>

        <form
            action=""
            method="post"
            id="registrationForm"
            onsubmit="return validateRegistrationForm();"
            novalidate
        >

            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
            <span class="error" id="nameError"><?php echo $nameError; ?></span>

            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <span class="error" id="emailError"><?php echo $emailError; ?></span>

            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <span class="error" id="passwordError"><?php echo $passwordError; ?></span>

            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm">
            <span class="error" id="confirmError"><?php echo $confirmError; ?></span>

            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
            <span class="error" id="addressError"><?php echo $addressError; ?></span>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <span class="error" id="phoneError"><?php echo $phoneError; ?></span>

            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="">-- Select Role --</option>
                <option value="member" <?php echo $role === "member" ? "selected" : ""; ?>>Member</option>
                <option value="admin" <?php echo $role === "admin" ? "selected" : ""; ?>>Admin</option>
            </select>
            <span class="error" id="roleError"><?php echo $roleError; ?></span>

            <input type="submit" name="mysubmit" value="Register">

        </form>

        <p class="form-footer-link">
            Already have an account? <a href="login.php">Log in</a>
        </p>

    </div>

    <script src="../public/js/validation.js"></script>

</body>
</html>
