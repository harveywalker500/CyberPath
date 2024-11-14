<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session

// Check if the user is logged in
if (!check_login()) {
    header('Location: loginForm.php');
    exit();
}

// Get the current user's ID from the session
$userID = get_session('userID');

// Fetch the episodes from the database
try {
    $episodes = getEpisodes();
} catch (Exception $e) {
    $episodes = [];
    $errors[] = "Error fetching episodes: " . $e->getMessage();
}

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// If there were any errors, display them
if (isset($errors) && !empty($errors)) {
    echo show_errors($errors);
}

?>

<!-- Homepage content -->
<div class="container">
    <h1 class="title has-text-centered">Welcome to CyberPath!</h1>
    <p class="subtitle has-text-centered">Your journey through cybersecurity stories and quizzes begins here.</p>

    <!-- Display the episodes dynamically -->
    <div class="content">
        <h2>Available Episodes:</h2>
        <ul>
            <?php if (!empty($episodes)): ?>
                <?php foreach ($episodes as $episodeID => $episodeName): ?>
                    <li>
                        <a href="storySelect.php?episodeID=<?= htmlspecialchars($episodeID) ?>"><?= htmlspecialchars($episodeName) ?></a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No episodes available at the moment.</p>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Display user's progress -->
    <div class="content">
        <h3>Your Progress:</h3>
        <?php
        try {
            $progress = getUserProgress($userID);
            if ($progress) {
                echo "<p>Story Completed: Episode " . htmlspecialchars($progress['storyCompleted']) . "</p>";
                echo "<p>Quiz Completed: Episode " . htmlspecialchars($progress['quizCompleted']) . "</p>";
            } else {
                echo "<p>No progress found. Start an episode to begin!</p>";
            }
        } catch (Exception $e) {
            echo "<p>Error fetching progress: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</div>

<?php
echo makeFooter("This is the footer.");
echo makePageEnd();
?>
