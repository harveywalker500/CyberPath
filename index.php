<?php
// Include the functions file
require_once("functions.php");
session_start(); //Starts the session.
echo makePageStart("CyberPath");

echo makeNavMenu("CyberPath");
?>

<h1>Hello Mat!</h1>
<p>...</p>
<script src="index.js"></script>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
