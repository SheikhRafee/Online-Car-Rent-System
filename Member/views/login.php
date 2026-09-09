<?php
/* =====================================================================
   views/login.php
   ---------------------------------------------------------------------
   The login screen.

   A VIEW's only job is to PRINT. All the thinking happens in
   controllers/login_control.php, which is included on the very first
   line below. By the time the HTML starts, that controller has already:

       - handled ?logout=true if that is why we are here
       - logged the visitor straight back in from a Remember-me cookie,
         and bounced them to the homepage if they were already signed in
       - checked the email and password, and redirected on success
       - filled $emailError / $passwordError with anything that failed
       - put the typed email back into $email so it does not have to be
         retyped

   So this file contains no  if  statements except tiny ones that ask
   "is there a message to print?".

   The form posts to itself (action=""), so the browser sends it back to
   login.php and login_control.php picks it up again.

   NOTE: the submit button is  name="mysubmit" . That name is not
   decoration - the controller uses isset($_POST["mysubmit"]) to tell a
   real submission apart from a first visit.
   ===================================================================== */

require_once __DIR__ . "/../controllers/login_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login &mdash; Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css">

</head>

<body>

    <div class="form-box">

        <h2>Login</h2>

        <!--
            Green banner, shown only when we arrived from a successful
            registration. reg_control.php redirects to
            login.php?registered=1 , so the 1 in the URL is the signal.
        -->
        <?php if (isset($_GET["registered"])) { ?>
            <p class="form-success">Account created &mdash; you can log in now.</p>
        <?php } ?>

        <!--
            novalidate switches OFF the browser's own built-in bubbles,
            so our own JavaScript messages are the ones the user sees.
            onsubmit runs validateLoginForm(); if that returns false the
            form is never sent.
        -->
        <form action="" method="post" id="loginForm"
              onsubmit="return validateLoginForm();" novalidate>

            <label for="email">Email</label>
            <input type="text" id="email" name="email"
                   value="<?php echo htmlspecialchars($email); ?>"
                   placeholder="Enter your email">
            <span class="error" id="emailError"><?php echo htmlspecialchars($emailError); ?></span>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password">
            <span class="error" id="passwordError"><?php echo htmlspecialchars($passwordError); ?></span>

            <!--
                Ticking this makes login_control.php call
                setRememberMeCookie(), which drops a signed cookie that
                lasts thirty days. See models/session_helper.php.
            -->
            <label class="checkbox-label" for="remember">
                <input type="checkbox" id="remember" name="remember">
                Remember me
            </label>

            <input type="submit" name="mysubmit" value="Login">

        </form>

        <p class="small">
            Don't have an account? <a href="registration.php">Register</a>
        </p>

    </div>

    <script src="../public/js/validation.js"></script>

</body>

</html>
