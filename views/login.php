<?php

require "../controllers/login_control.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Car Rental</title>

    <link
        rel="stylesheet"
        href="../public/css/style.css?v=<?php
            echo @filemtime(
                __DIR__ .
                "/../public/css/style.css"
            );
        ?>"
    >

</head>


<body>

    <div class="form-box">

        <h2>Login</h2>

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


            <!-- EMAIL -->

            <label for="email">

                Email:

            </label>


            <input
                type="text"
                id="email"
                name="email"
                value="<?php
                    echo htmlspecialchars($email);
                ?>"
                placeholder="Enter your email"
            >


            <span class="error" id="emailError">

                <?php
                    echo htmlspecialchars(
                        $emailError
                    );
                ?>

            </span>



            <!-- PASSWORD -->

            <label for="password">

                Password:

            </label>


            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
            >


            <span class="error" id="passwordError">

                <?php
                    echo htmlspecialchars(
                        $passwordError
                    );
                ?>

            </span>



            <!-- REMEMBER ME -->

            <label class="checkbox-label" for="remember">

                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                >

                Remember me

            </label>



            <!-- LOGIN -->

            <input
                type="submit"
                name="mysubmit"
                value="Login"
            >


        </form>


        <p class="small">

            Don't have an account?

            <a href="registration.php">

                Register

            </a>

        </p>


    </div>

    <script src="../public/js/validation.js"></script>

</body>

</html>