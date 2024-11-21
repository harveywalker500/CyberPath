<?php
// Include the functions file
require_once("functions.php");
session_start(); //Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page


echo makePageStart("CyberPath", "../../css/stylesheet.css");
echo makeNavMenu("CyberPath");
?>

<p>...</p>

<?php
echo makeFooter();
echo makePageEnd();
?>