<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

echo makeNavMenu();


echo makeFooter("This is the footer");
echo makePageEnd();
?>