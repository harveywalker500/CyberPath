<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// Check if the user is logged in
if (isset($_SESSION['userID'])) {
    // If the user is logged in, show a personalized greeting
    $userID = $_SESSION['userID'];
    $username = getUserUsername($userID); // Assuming a function that fetches the username
    echo "<h1>Hello, $username!</h1>";
    echo "<p>Welcome back to CyberPath!</p>";
} else {
    // If the user is not logged in, show a welcome message
    echo "<h1>Welcome to CyberPath!</h1>";
    echo "<p>Explore our platform and enhance your cybersecurity knowledge.</p>";
}

echo "<p>More content goes here...</p>";
echo "<script src='index.js'></script>";

echo makeFooter("This is the footer with a registration link.");
echo makePageEnd();
?>
