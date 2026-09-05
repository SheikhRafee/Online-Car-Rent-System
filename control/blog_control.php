<?php
$author = $title = $content = "";
$authorError = $titleError = $contentError = "";
$formIsValid = false;

// Only process when SUBMIT button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $formIsValid = true;

    if (empty($_POST["title"])) {
        $titleError = "Title is required";
        $formIsValid = false;
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $_POST["title"])) {
        $titleError = "Only letter and white spaces are allowed";
        $formIsValid = false;
    } else {
        $title = test_value($_POST["title"]);
    }

    if (empty($_POST["author"])) {
        $authorError = "Author is required";
        $formIsValid = false;
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $_POST["author"])) {
        $authorError = "Only letter and white spaces are allowed";
        $formIsValid = false;
    } else {
        $author = test_value($_POST["author"]);
    }

    if (empty($_POST["content"])) {
        $contentError = "Content is required";
        $formIsValid = false;
    } else {
        $content = test_value($_POST["content"]);
    }

    if ($formIsValid) {
        $sql = "INSERT INTO blogs (author, title, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $author, $title, $content);
        
        if ($stmt->execute()) {
            $author = $title = $content = ""; // Clear form fields
        }
        $stmt->close();
    }
}

function test_value($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>