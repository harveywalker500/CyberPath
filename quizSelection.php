<?php
// Include the functions file
require_once("functions.php");

session_start(); //Starts the session.
loggedIn(); //Ensures the user is loggedIn before loading the page.

try {
    $episodes = getEpisodes();
} catch (Exception $e) {
    echo "An error occurred while fetching episodes: " . $e->getMessage();
    exit; // Exit if there's an error fetching episodes
}

echo makePageStart("CyberPath | Quiz Selection");
echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "quizSelection.php" => "Quiz Selection", "leaderboard.php"  => "Leaderboard"));
?>

<h1 class="title">Quiz Selection</h1>
<p>Select a quiz below to test your knowledge!</p>

<div class="container">
    <div class="columns is-multiline">
        <?php

        $userProgress = getUserProgress($_SESSION['userID']); // Get the user's progress
        $storyCompleted = $userProgress['storyCompleted'] ?? 0; // Get the story completion status
        
        // Loop through the episodes and set them based on the completion of story parts
        foreach ($episodes as $partNumber => $episodeTitle) {
            $isQuizUnlocked = ($storyCompleted >= $partNumber - 1);

            $buttonClass = $isCompleted ? "is-success" : "is-warning"; // Change button color based on completion status
            $buttonText = $isCompleted ? "Unlocked! Start Quiz" : "Locked, please complete part $partNumber of the story";
            $buttonState = $isCompleted ? "" : "disabled"; // Disable button if not completed
            $iconClass = $isCompleted ? "fas fa-check" : "fas fa-lock"; // Lock icon if locked, check icon if unlocked

            $quizLink = $isQuizUnlocked ? "episodeQuiz.php?episodeID=" . urlencode($partNumber) : "#";

            // Display each quiz with the corresponding story title
            echo <<<HTML
            <div class="column is-full-mobile is-half-tablet is-one-third-desktop">
                <div class="box has-text-centered">
                    <p class="title is-5">$episodeTitle</p>
                    <a href="$quizLink" class="button $buttonClass" $buttonState>
                        <span class="icon"><i class="$iconClass"></i></span>
                        <span>$buttonText</span>
                    </a>
                </div>
            </div>
HTML;
        }
        ?>
    </div>
</div>


<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
