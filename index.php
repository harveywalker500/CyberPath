<?php
// Include the functions file
require_once("functions.php");
echo makePageStart("CyberPath", "stylesheet.css");

echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "leaderboard.php" => "Leaderboard"));
?>

<h1>Hello Matthew!</h1>
<p>...</p>
<script src="index.js"></script>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
