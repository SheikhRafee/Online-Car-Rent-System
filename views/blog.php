<?php
require_once __DIR__ . "/../models/session_helper.php";
require_once __DIR__ . "/../controllers/blog_control.php";
require_once __DIR__ . "/../controllers/blog_delete_control.php";



$currentUser = requireLogin();
$posts = getAllBlogs($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog &mdash; Car Rental</title>
<link rel="stylesheet" href="../public/css/style.css">
<link rel="stylesheet" href="../public/css/blog.css">
</head>
<body class="homepage">
<header class="homepage-header">
    <div class="logo">Car<span>Rental</span></div>
    <nav>
        <a href="homepage.php">Home</a>
        <a href="order_history.php">Order History</a>
        <a href="blog.php" class="active">Blog</a>
        <a href="profile.php">Profile</a>
        <a href="../controllers/login_control.php?logout=true">Logout</a>
    </nav>
</header>

<main class="rental-container">
    <h1>Blog</h1>

    <?php if (isset($_GET["posted"])) { ?>
        <p class="success">Your post has been published.</p>
    <?php } ?>
    <?php if (isset($_GET["deleted"])) { ?>
        <p class="success">Post deleted.</p>
    <?php } ?>
    <?php if (isset($deleteError)) { ?>
        <p class="error"><?php echo htmlspecialchars($deleteError); ?></p>
    <?php } ?>

    <!-- ---------- write a post ---------- -->
    <section class="blog-form">
        <h2>Write a post</h2>
        <p>Posting as <strong><?php echo htmlspecialchars($currentUser["name"]); ?></strong></p>

        <form id="blogForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">

            <label>Title</label><br>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title ?? ''); ?>">
            <span class="error" id="titleError"><?php echo $titleError ?? ''; ?></span><br><br>

            <label>Content</label><br>
            <textarea name="content" id="content" rows="8" cols="50"><?php echo htmlspecialchars($content ?? ''); ?></textarea>
            <span class="error" id="contentError"><?php echo $contentError ?? ''; ?></span><br><br>

            <input type="submit" name="submit" value="Publish">
        </form>
    </section>

    <!-- ---------- all posts ---------- -->
    <section class="blog-list" id="postsContainer">
        <h2>All blog posts</h2>
        <?php if ($posts->num_rows > 0) { ?>
            <?php while ($row = $posts->fetch_assoc()) { ?>
                <article class="post">
                    <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                    <p class="post-meta">
                        By <?php echo htmlspecialchars($row["author_name"]); ?>
                        on <?php echo htmlspecialchars($row["created_at"]); ?>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>

                    <?php if ($currentUser["role"] == "admin" || $row["user_id"] == $currentUser["id"]) { ?>
                        <form method="POST" class="deleteForm">
                            <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
                            <input type="hidden" name="delete_id" value="<?php echo (int) $row["id"]; ?>">
                            <input type="submit" name="delete" value="Delete">
                        </form>
                    <?php } ?>
                </article>
            <?php } ?>
        <?php } else { ?>
            <p>No blog posts yet.</p>
        <?php } ?>
    </section>
</main>

<?php include "footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {

    // Submit new post
    $("#blogForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: "../controllers/blog_control.php",
            method: "POST",
            data: $(this).serialize() + "&submit=1&ajax=1",
            dataType: "json",
            success: function (res) {
                $("#titleError").text((res.errors && res.errors.title) || "");
                $("#contentError").text((res.errors && res.errors.content) || "");
                if (res.success) {
                    $("#blogForm")[0].reset();
                    $("#postsContainer").load(location.href + " #postsContainer > *");
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = "../login.php";
                } else {
                    alert("Something went wrong publishing your post.");
                }
            }
        });
    });

    // Delete a post
    $("#postsContainer").on("submit", "form.deleteForm", function (e) {
        e.preventDefault();
        var $form = $(this);

        $.ajax({
            url: "../controllers/blog_delete_control.php",
            method: "POST",
            data: $form.serialize() + "&delete=1&ajax=1",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $form.closest("article.post").remove();
                } else {
                    alert(res.error || "Delete failed.");
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = "../login.php";
                } else if (xhr.status === 403) {
                    alert("You don't have permission to delete this post.");
                } else {
                    alert("Something went wrong. Please try again.");
                }
            }
        });
    });

});
</script>
</body>
</html>