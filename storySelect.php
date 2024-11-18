<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

try {
    $episodes = getEpisodes();
} catch (Exception $e) {
    echo "An error occurred while fetching episodes: " . $e->getMessage();
    exit; // Exit if there's an error fetching episodes
}

echo makePageStart("CyberPath | Story Selection", "stylesheet.css");
echo makeNavMenu("CyberPath");
?>

<h1 class="title">Story Selection</h1>
<p>Select a story to begin!</p>

<div class="container">
    <div class="columns is-multiline">
        <?php

        $userProgress = getUserProgress($_SESSION['userID']); // Get the user's progress
        $quizCompleted = $userProgress['quizCompleted'] ?? 0; // Get the story completion status
        
        // Loop through the episodes and set them based on the completion of story parts
        foreach ($episodes as $partNumber => $episodeTitle) {
            // Logic for unlocking the episode
            if ($partNumber == 1) {
                // Story 1 is always unlocked
                $isStoryUnlocked = true;
            } else {
                // For other stories, ensure the user has completed the previous quiz
                $isStoryUnlocked = ($quizCompleted >= $partNumber - 1);
            }
        
            $buttonClass = $isStoryUnlocked  ? "is-success" : "is-warning";
            $buttonText = $isStoryUnlocked  ? "Unlocked! Start Quiz" : "Locked, please complete part " . ($partNumber - 1) . " of the quiz";
            $buttonState = $isStoryUnlocked  ? "" : "disabled";
            $iconClass = $isStoryUnlocked  ? "fas fa-check" : "fas fa-lock";
            
            // Use a form to submit episodeID via POST
            echo <<<HTML
            <div class="column is-full-mobile is-half-tablet is-one-third-desktop">
                <div class="box has-text-centered">
                    <p class="title is-5">$episodeTitle</p>
                    <form action="episodeStory.php" method="POST" style="display:inline;">
                        <input type="hidden" name="episodeID" value="$partNumber">
                        <button type="submit" class="button $buttonClass" $buttonState>
                            <span class="icon"><i class="$iconClass"></i></span>
                            <span>$buttonText</span>
                        </button>
                    </form>
                </div>
            </div>
        HTML;
        }
        
        ?>
    </div>
</div>

<?php
echo makeFooter();
echo makePageEnd();
?>
