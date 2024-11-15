<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// Check if the session variable for username is set (i.e., the user is logged in)
if (isset($_SESSION['username'])) {
    // User is logged in, show personalized message
    echo "<div class='container'>";
    echo "<section class='hero is-info is-bold'>";
    echo "<div class='hero-body'>";
    echo "<div class='container'>";
    echo "<h1 class='title is-1'>Welcome back, " . htmlspecialchars($_SESSION['username']) . "!</h1>";
    echo "</div>";
    echo "</div>";
    echo "</section>";

    echo "<section class='section'>";
    echo "<div class='content'>";
    echo "<p class='has-text-weight-semibold'>Hello, " . htmlspecialchars($_SESSION['username']) . "! Continue your journey!</p>";
    echo "</div>";
    echo "</section>";
    echo "</div>";
} else {
    // User is not logged in, show generic message
    echo "<div class='container'>";
    echo "<section class='hero is-info is-bold'>";
    echo "<div class='hero-body'>";
    echo "<div class='container'>";
    echo "<h1 class='title is-1'>Welcome to CyberPath!</h1>";
    echo "</div>";
    echo "</div>";
    echo "</section>";

    echo "<section class='section'>";
    echo "<div class='content'>";
    echo "<p>Welcome to CyberPath, your destination for interactive learning and challenges in cybersecurity. Whether you're a beginner or a seasoned pro, we have a range of stories and quizzes designed to test your knowledge and improve your skills.</p>";
    echo "<p>If you're new here, you can <a href='register.php'>sign up</a> to start your journey. If you're returning, please <a href='loginForm.php'>log in</a> to continue where you left off.</p>";
    echo "</div>";
    echo "</section>";
    echo "</div>";
}

echo makeFooter();
echo makePageEnd();
?>
