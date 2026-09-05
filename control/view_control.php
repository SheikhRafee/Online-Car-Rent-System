<?php
require_once "../config/db.php";
require_once "../control/blog_control.php";
require_once "../control/delete_control.php";
?>
<h2>All Blog Posts</h2>

<?php
    $sql = "SELECT * FROM blogs ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
?>
    <div class="post">
        <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
        <p>
            <strong>Author:</strong>
            <?php echo htmlspecialchars($row["author"]); ?>
        </p>
        <p>
            <?php echo nl2br(htmlspecialchars($row["content"])); ?>
        </p>

        <p>
            <strong>Posted Time:</strong>
            <?php echo htmlspecialchars($row["posted_time"]); ?>
        </p>
        <!-- Delete button -->
        <form method="POST">
            <input type="hidden" name="delete_id" value="<?php echo $row["id"]; ?>">
            <input type="submit" name="delete" value="Delete">
        </form>
    </div>
    <hr>
<?php
}

} else {
echo "<p>No blog posts found.</p>";
}
?>