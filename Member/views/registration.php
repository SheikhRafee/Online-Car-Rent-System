<?php
/* =====================================================================
   views/registration.php
   ---------------------------------------------------------------------
   The "create an account" screen.

   Same shape as login.php: controllers/reg_control.php runs first and
   works everything out, this file only prints.

   Three things worth noticing in the HTML below:

     1. Every text box has  value="<?php echo htmlspecialchars($x); ?>"
        That is what makes the form remember what you typed when one
        field was wrong - without it, a single mistake would wipe the
        whole form and you would start again.

     2. htmlspecialchars() wraps EVERY value we print. That is our XSS
        protection: if somebody registers with the name
            <script>alert("hacked")</script>
        htmlspecialchars turns the < and > into &lt; and &gt; so the
        browser shows it as text instead of running it as code.

     3. The field names here are fixed by reg_control.php. In particular
        the confirm box is  name="confirm"  (not confirm_password), and
        the submit button is  name="mysubmit" , which is how the
        controller knows the form was actually sent.
   ===================================================================== */

require_once __DIR__ . "/../controllers/reg_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register &mdash; Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css">

</head>

<body>

    <div class="form-box">

        <h2>Create an Account</h2>

        <form action="" method="post" id="registrationForm"
              onsubmit="return validateRegistrationForm();" novalidate>

            <label for="name">Name</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars($name); ?>">
            <span class="error" id="nameError"><?php echo htmlspecialchars($nameError); ?></span>

            <label for="email">Email</label>
            <input type="text" id="email" name="email"
                   value="<?php echo htmlspecialchars($email); ?>">
            <span class="error" id="emailError"><?php echo htmlspecialchars($emailError); ?></span>

            <label for="password">Password (at least 8 characters)</label>
            <input type="password" id="password" name="password">
            <span class="error" id="passwordError"><?php echo htmlspecialchars($passwordError); ?></span>

            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm">
            <span class="error" id="confirmError"><?php echo htmlspecialchars($confirmError); ?></span>

            <label for="address">Address</label>
            <input type="text" id="address" name="address"
                   value="<?php echo htmlspecialchars($address); ?>">
            <span class="error" id="addressError"><?php echo htmlspecialchars($addressError); ?></span>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone"
                   value="<?php echo htmlspecialchars($phone); ?>">
            <span class="error" id="phoneError"><?php echo htmlspecialchars($phoneError); ?></span>

            <label for="role">Role</label>
            <select id="role" name="role">

                <!--
                    Each option carries an explicit value="..." so the
                    browser sends "member" / "admin" exactly as the
                    database column expects, whatever text we display.

                    The first option has an EMPTY value, so somebody who
                    never opens the dropdown sends "" and gets an error
                    instead of silently becoming a member.
                -->
                <option value="">-- Select Role --</option>
                <option value="member" <?php if ($role === "member") { echo "selected"; } ?>>Member</option>
                <option value="admin"  <?php if ($role === "admin")  { echo "selected"; } ?>>Admin</option>

            </select>
            <span class="error" id="roleError"><?php echo htmlspecialchars($roleError); ?></span>

            <input type="submit" name="mysubmit" value="Register">

        </form>

        <p class="form-footer-link">
            Already have an account? <a href="login.php">Log in</a>
        </p>

    </div>

    <script src="../public/js/validation.js"></script>

</body>

</html>
