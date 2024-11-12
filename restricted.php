<?php
    //ini_set("session.save_path", "0;644;/var/www/html/sessionData");
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        require_once("functions.php");
        if (check_login())
        {
            echo "<p>Welcome to restricted page, ". $_SESSION['username']."</p>\n";
        }
        else{
            echo "<p>User must be logged in to access this page.</p>\n";
            echo "<a href='loginForm.php'>Back to Login!</a>\n";
        }
        echo "<button type='button'>Log Out</button>\n";
    ?>
</body>
</html>