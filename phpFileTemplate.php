<?php
// Include the functions file
require_once("functions.php");
session_start(); //Starts the session.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");
?>

<p>...</p>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>