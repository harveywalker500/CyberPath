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
        if (isset($_SESSION['firstname'])) {
            echo $_SESSION['firstname'];
        } else {
            echo "<p>No session variable set</p>\n";
        }
    ?>
</body>
</html>