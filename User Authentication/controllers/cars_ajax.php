<?php
/**
 * AJAX endpoint for category filtering on the home page.
 *
 * Called from public/js/category_filter.js via XMLHttpRequest.
 * Returns an HTML fragment (car cards) which JS drops straight into
 * #carListContainer — same style as the faculty's search_control.php
 * example, but using getAvailableCars(), which already goes through
 * a prepared statement in models/db.php.
 */

require_once "../models/db.php";
require_once "../models/session_helper.php";

// Still an authenticated page — don't let a logged-out request pull data.
requireLogin();

$db   = new mydb();
$conn = $db->openConn();

$type = isset($_GET["type"]) ? trim($_GET["type"]) : "All";
$cars = $db->getAvailableCars($conn, $type);

$conn->close();

if (empty($cars)) {
    echo '<p class="no-results">No available cars in this category right now.</p>';
    return;
}

foreach ($cars as $car) {
    ?>
    <article class="car-card">

        <div class="car-image-container">

            <div class="discount">Featured</div>

            <?php if (!empty($car['image_path'])) { ?>
                <img
                    src="../public/uploads/cars/<?php echo htmlspecialchars($car['image_path']); ?>"
                    alt="<?php echo htmlspecialchars($car['name']); ?>"
                >
            <?php } else { ?>
                <div class="no-image">No image yet</div>
            <?php } ?>

            <h3><?php echo htmlspecialchars($car['name']); ?></h3>

        </div>

        <div class="car-details">

            <h2><?php echo strtoupper(htmlspecialchars($car['type'])); ?></h2>

            <p class="car-model">Model: <?php echo htmlspecialchars($car['model']); ?></p>

            <p class="insurance">Mandatory insurance available</p>

            <p class="description"><?php echo htmlspecialchars($car['description'] ?? ''); ?></p>

            <div class="car-bottom">

                <div class="price">
                    <small>FROM</small>
                    <strong><?php echo number_format($car['price_per_day']); ?> BDT</strong>
                    <small>/ DAY</small>
                </div>

                <a href="#" class="details-button">VIEW DETAILS</a>

            </div>

        </div>

    </article>
    <?php
}