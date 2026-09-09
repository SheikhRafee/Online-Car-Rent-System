<?php
/* =====================================================================
   views/homepage.php
   ---------------------------------------------------------------------
   The car list. This is where a member lands after logging in.

   controllers/home_control.php has already prepared three things for us:
       $categories        the category buttons to draw
       $selectedCategory  which one is currently active
       $cars              the cars to show

   All this file does is loop over $cars and print a card for each one.
   ===================================================================== */

require_once __DIR__ . "/../controllers/home_control.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Car Rental &mdash; Home</title>

    <link rel="stylesheet" href="../public/css/style.css">

</head>

<body class="homepage">

    <!-- ================= HEADER ================= -->

    <header class="homepage-header">

        <div class="logo">
            Car<span>Rental</span>
        </div>

        <nav>

            <a href="homepage.php" class="active">Home</a>

            <?php if (file_exists("cars.php")) { ?>
                <a href="cars.php">Cars</a>
            <?php } ?>

            <a href="order_history.php">Order History</a>

            <!--
                The blog is another student's task. file_exists() means
                the link only appears once blog.php has been added to
                the View folder, so we never show a dead link.
            -->
            <?php if (file_exists("blog.php")) { ?>
                <a href="blog.php">Blog</a>
            <?php } ?>

            <a href="profile.php">Profile</a>

            <a href="../controllers/login_control.php?logout=true">Logout</a>

        </nav>

    </header>

    <!-- ================= MAIN ================= -->

    <main class="rental-container">

        <!-- ========== SEARCH / FILTER ========== -->
        <!--
            A plain GET form. Choosing "Microbus" and pressing the
            button sends the browser to  homepage.php?type=Microbus ,
            which home_control.php reads back out of the URL.
            GET is right here because searching only READS data - it
            changes nothing, and the result is a link you can bookmark.
        -->

        <div class="search-sidebar">

            <h3>Search by</h3>

            <form method="get" action="homepage.php">

                <div class="search-fields">

                    <div class="field-group">

                        <label for="categorySelect">Category</label>

                        <select id="categorySelect" name="type">

                            <?php foreach ($categories as $category) { ?>

                                <option
                                    value="<?php echo htmlspecialchars($category); ?>"
                                    <?php if ($category == $selectedCategory) { echo "selected"; } ?>
                                >
                                    <?php echo htmlspecialchars($category); ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <button type="submit" class="search-button">Look for</button>

                </div>

            </form>

        </div>

        <!-- ========== CAR LIST ========== -->

        <section class="car-list">

            <div class="page-title">

                <div>
                    <h1>Car rental</h1>
                    <p>Find the perfect car for your journey.</p>
                </div>

                <span class="welcome">
                    Welcome, <?php echo htmlspecialchars($currentUser["name"]); ?>
                </span>

            </div>

            <!-- Category buttons - the same filter, as clickable links -->

            <div class="category-bar">

                <?php foreach ($categories as $category) { ?>

                    <!--
                        urlencode() makes the value safe to sit inside a
                        URL: a space becomes %20, so "Private Car"
                        travels correctly instead of breaking the link.
                    -->
                    <a
                        href="homepage.php?type=<?php echo urlencode($category); ?>"
                        class="category-link<?php if ($category == $selectedCategory) { echo " active"; } ?>"
                    >
                        <?php echo htmlspecialchars($category); ?>
                    </a>

                <?php } ?>

            </div>

            <h2 class="section-title">
                <?php
                if ($selectedCategory == "All") {
                    echo "Featured Cars";
                } else {
                    echo htmlspecialchars($selectedCategory) . " Cars";
                }
                ?>
            </h2>

            <!-- Shown only when the chosen category has no cars in it -->

            <?php if (count($cars) == 0) { ?>
                <p class="no-results">No available cars in this category right now.</p>
            <?php } ?>

            <!--
                One <article> per car. foreach walks through the $cars
                array and gives us one car at a time as $car.
            -->

            <?php foreach ($cars as $car) { ?>

                <article class="car-card">

                    <!-- ---------- Image ---------- -->

                    <div class="car-image-container">

                        <div class="discount">Featured</div>

                        <?php
                        /*
                           Two things have to be true before we print an
                           <img>: the database must hold a file name, AND
                           that file must actually be sitting in the
                           Uploads folder. Checking both is what stops
                           the page filling up with broken-image icons
                           when a picture has not been uploaded yet.
                        */
                        $imageFile = "../public/uploads/cars/" . $car["image_path"];
                        ?>

                        <?php if ($car["image_path"] != "" && file_exists($imageFile)) { ?>

                            <img
                                src="<?php echo htmlspecialchars($imageFile); ?>"
                                alt="<?php echo htmlspecialchars($car["name"]); ?>"
                            >

                        <?php } else { ?>

                            <div class="no-image">No image yet</div>

                        <?php } ?>

                        <h3><?php echo htmlspecialchars($car["name"]); ?></h3>

                    </div>

                    <!-- ---------- Information ---------- -->

                    <div class="car-details">

                        <h2><?php echo htmlspecialchars(strtoupper($car["type"])); ?></h2>

                        <p class="car-model">
                            Model: <?php echo htmlspecialchars($car["model"]); ?>
                        </p>

                        <p class="insurance">Mandatory insurance available</p>

                        <p class="description">
                            <?php echo htmlspecialchars($car["description"]); ?>
                        </p>

                        <div class="car-bottom">

                            <div class="price">
                                <small>FROM</small>
                                <strong><?php echo number_format($car["price_per_day"]); ?> BDT</strong>
                                <small>/ DAY</small>
                            </div>

                            <!-- The id in this link is what car_details.php reads. -->
                            <a href="car_details.php?id=<?php echo $car["id"]; ?>" class="details-button">
                                VIEW DETAILS
                            </a>

                        </div>

                    </div>

                </article>

            <?php } ?>

        </section>

    </main>

    <!-- ================= FOOTER ================= -->

    <footer class="homepage-footer">
        <p>&copy; 2026 Car Rental System. All Rights Reserved.</p>
    </footer>

</body>

</html>
