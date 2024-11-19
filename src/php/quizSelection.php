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

echo makePageStart("CyberPath | Quiz Selection", "../../css/stylesheet.css");
echo makeNavMenu("CyberPath");
?>
<div class="displayBody">
<h1 class="title">Quiz Selection</h1>
<p>Select a quiz below to test your knowledge!</p>

<div class="container">
    <div class="columns is-multiline">
        <?php

        $userProgress = getUserProgress($_SESSION['userID']); // Get the user's progress
        $storyCompleted = $userProgress['storyCompleted'] ?? 0; // Get the story completion status
        
        // Loop through the episodes and set them based on the completion of story parts
        foreach ($episodes as $partNumber => $episodeTitle) {
            $isQuizUnlocked = ($storyCompleted >= $partNumber);
        
            $buttonClass = $isQuizUnlocked  ? "is-success" : "is-warning";
            $buttonText = $isQuizUnlocked  ? "Unlocked! Start Quiz" : "Locked, please complete part $partNumber of the story";
            $buttonState = $isQuizUnlocked  ? "" : "disabled";
            $iconClass = $isQuizUnlocked  ? "fas fa-check" : "fas fa-lock";
            
            // Use a form to submit episodeID via POST
            echo <<<HTML
            <div class="column is-full-mobile is-half-tablet is-one-third-desktop">
                <div class="box has-text-centered">
                    <p class="title is-5">$episodeTitle</p>
                    <form action="episodeQuiz.php" method="POST" style="display:inline;">
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
</div> 


<?php
echo makeFooter();
echo makePageEnd();
?>
