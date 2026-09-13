<?php
   
require_once "../models/db.php";
require_once "../models/session_helper.php";
ini_set('display_errors', 1);
   error_reporting(E_ALL);
$currentUser = requireLogin();

$db   = new mydb();
$conn = $db->openConn();

$categories = $db->getDistinctCarTypes($conn);
// "All" isn't a real car type sitting in the database — it's
// added here in PHP so the category bar has a way to show every car.
array_unshift($categories, "All");

$selectedCategory = isset($_GET["type"]) ? trim($_GET["type"]) : "All";
if (!in_array($selectedCategory, $categories, true)) {
    $selectedCategory = "All";
}

$cars = $db->getAvailableCars($conn, $selectedCategory);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Car Rental</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?php echo @filemtime(__DIR__ . "/../public/css/style.css"); ?>">

</head>

<body class="homepage">

    <header class="homepage-header">
        <div class="logo">
            Car<span>Rental</span>
        </div>
        <nav>
            <a href="homepage.php" class="active">Home</a>
            <a href="cars.php">Cars</a>
            <a href="order_history.php">Order History</a>
            <a href="blog.php">Blog</a>
            <a href="profile.php">Profile</a>
            <a href="../controllers/login_control.php?logout=true">
                Logout
            </a>
        </nav>
    </header>

    <main class="rental-container">
        <div class="search-sidebar">
            <h3>Search by</h3>
            <div class="search-fields">
                <div class="field-group">
                    <label>Category</label>
                    <select id="categorySelect">
                        <?php foreach ($categories as $category) { ?>
                            <option <?php echo $category === $selectedCategory ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field-group">
                    <label>Collection</label>
                    <input type="date">
                </div>
                <div class="field-group">
                    <label>Delivery</label>
                    <input type="date">
                </div>
                <button class="search-button">
                    Look for
                </button>
            </div>
        </div>

        <section class="car-list">
            <div class="page-title">
                <div>
                    <h1>Car rental</h1>
                    <p>
                        Find the perfect car for your journey.
                    </p>
                </div>
                <span class="welcome">
                    Welcome, <?php echo htmlspecialchars($currentUser["name"]); ?>
                </span>
            </div>

            <div class="category-bar">
                <?php foreach ($categories as $category) { ?>
                    <a
                        href="homepage.php?type=<?php echo urlencode($category); ?>"
                        class="category-link<?php echo $category === $selectedCategory ? " active" : ""; ?>"
                    >
                        <?php echo htmlspecialchars($category); ?>
                    </a>
                <?php } ?>
            </div>

            <h2 class="section-title">
                <?php echo $selectedCategory === "All" ? "Featured Cars" : htmlspecialchars($selectedCategory) . " Cars"; ?>
            </h2>

            <div id="carListContainer">
                <?php if (empty($cars)) { ?>
                    <p class="no-results">No available cars in this category right now.</p>
                <?php } ?>
                <?php foreach ($cars as $car) { ?>
                    <article class="car-card">
                        <div class="car-image-container">
                            <div class="discount">
                                Featured
                            </div>
                            <?php if (!empty($car['image_path'])) { ?>
                                <img
                                    src="../public/uploads/cars/<?php echo htmlspecialchars($car['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($car['name']); ?>"
                                >
                            <?php } else { ?>
                                <div class="no-image">No image yet</div>
                            <?php } ?>
                            <h3>
                                <?php echo htmlspecialchars($car['name']); ?>
                            </h3>
                        </div>
                        <div class="car-details">
                            <h2>
                                <?php echo strtoupper(htmlspecialchars($car['type'])); ?>
                            </h2>
                            <p class="car-model">
                                Model: <?php echo htmlspecialchars($car['model']); ?>
                            </p>
                            <p class="insurance">
                                Mandatory insurance available
                            </p>
                            <p class="description">
                                <?php echo htmlspecialchars($car['description'] ?? ''); ?>
                            </p>
                            <div class="car-bottom">
                                <div class="price">
                                    <small>FROM</small>
                                    <strong>
                                        <?php echo number_format($car['price_per_day']); ?>
                                        BDT
                                    </strong>
                                    <small>/ DAY</small>
                                </div>
                                <a href="#" class="details-button">
                                    VIEW DETAILS
                                </a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </section>
    </main>

    <footer class="homepage-footer">
        <p>
            &copy; 2026 Car Rental System. All Rights Reserved.
        </p>
    </footer>

    <script src="../public/js/category_filter.js"></script>

</body>

</html>