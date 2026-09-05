<?php
require_once "../config/db.php";
require_once "../control/blog_control.php";
require_once "../control/delete_control.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Blog Page</title>
        <link rel = "stylesheet" href = "../css/style.css">
    </head>
    <body>
        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = "POST">
            Author: <input type = "text" name = "author" value = "<?php echo $author;?>"> 
            <span class = "error">* <?php echo $authorError?></span><br><br>

            Title: <input type = "text" name = "title" value = "<?php echo $title;?>"> 
            <span class = "error">* <?php echo $titleError?></span><br><br>

            Content: <textarea name = "content" rows = 10 cols = 30><?php echo $content;?></textarea>
            <span class = "error">* <?php echo $contentError?></span><br><br>

            <input type = "submit" name = "submit" value = "Submit">
        </form>
        <?php 
        if(isset($_SERVER["REQUEST_METHOD"]) && $formIsValid == true){
            echo "Title: {$title}<br>";
            echo "Content: {$content}<br>";
            echo "Author: {$author} <br>";
            echo "Posted at " . date("Y-m-d H:i:s");
        }
        ?>
        <?php include "../control/view_control.php"; ?>
        <?php include "footer.php";?>
    </body>
</html>