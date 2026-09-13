<?php
function getAllBlogs($conn)
{
$sql = "SELECT blogs.*, users.name AS author_name
        FROM blogs
        JOIN users ON blogs.user_id = users.id
        ORDER BY blogs.id DESC";
return $conn->query($sql);
}

/* One post by its id. */
function findBlogById($id, $conn)
{
$sql = "SELECT * FROM blogs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
return $result->fetch_assoc();
}

/* Save a new post. Returns true or false. */
function insertBlog($author, $title, $content, $conn)
{
$sql = "INSERT INTO blogs (user_id, title, content) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iss", $user_id, $title, $content);
$ok = $stmt->execute();
$stmt->close();
return $ok;
}

/* Remove a post. */
function deleteBlog($id, $conn)
{
$sql = "DELETE FROM blogs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();
return $ok;
}
?>