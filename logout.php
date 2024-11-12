<?php
    session_start();
    require_once("functions.php");
    logOut();//Calls logout function
    header('Location: index.php');//Sends user to home page when logged out
    ?>