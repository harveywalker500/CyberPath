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
    
    // Show welcome message without repeating username
    echo "<section class='hero is-info is-bold'>";
    echo "<div class='hero-body'>";
    echo "<div class='container'>";
    echo "<div id='typeWriterEffect'><h1 class='title is-1'>Welcome back, " . htmlspecialchars($_SESSION['username']) . "!</h1></div>";
    echo "</div>";
    echo "</div>";
    echo "</section>";

    // Query to fetch the user's current story episode
    $userID = $_SESSION['userID']; // Assuming userID is stored in the session
    $dbConn = getConnection();

    // Fetch the current episode name based on user's story progress
    $sql = "
        SELECT e.episodeName
        FROM userProgressTable up
        JOIN episodesTable e ON up.storyCompleted = e.episodeID
        WHERE up.userID = :userID
    ";
    $stmt = $dbConn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $currentEpisode = $stmt->fetch(PDO::FETCH_ASSOC);

    // Display episode information if available
    if ($currentEpisode) {
        echo "<section class='section'>";
        echo "<div class='content'>";
        echo "<p class='has-text-weight-semibold'>You are currently up to: <strong>" . htmlspecialchars($currentEpisode['episodeName']) . "</strong></p>";
        echo "<p>Get ready to continue your journey with Captain Solara! The path ahead is filled with challenges and adventures that will test your skills and wit. Stay sharp and keep progressing!</p>";
        echo "</div>";
        echo "</section>";
    } else {
        echo "<section class='section'>";
        echo "<div class='content'>";
        echo "<p>It seems like you haven't completed any episodes yet. Start your journey and unlock your first story!</p>";
        echo "</div>";
        echo "</section>";
    }

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
