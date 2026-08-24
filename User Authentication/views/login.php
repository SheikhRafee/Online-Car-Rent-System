<?php
require_once "../controllers/login_control.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Car Rental</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?php echo @filemtime(__DIR__ . "/../public/css/style.css"); ?>">
</head>
<body>

    <div class="form-box">

        <h2>Welcome Back</h2>

        <?php if (isset($_GET["registered"])) { ?>
            <p class="form-success">Account created — you can log in now.</p>
        <?php } ?>

        <form
            action=""
            method="post"
            id="loginForm"
            onsubmit="return validateLoginForm();"
            novalidate
        >

            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <span class="error" id="emailError"><?php echo $emailError; ?></span>

            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <span class="error" id="passwordError"><?php echo $passwordError; ?></span>

            <label class="checkbox-label" for="remember">
                <input type="checkbox" id="remember" name="remember">
                Remember me
            </label>

            <input type="submit" name="mysubmit" value="Log In">

        </form>

        <p class="form-footer-link">
            Don't have an account? <a href="registration.php">Register</a>
        </p>

    </div>

    <script src="../public/js/validation.js"></script>

</body>
</html>
