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
        // Loop through the episodes and set them based on the completion of story parts
        foreach ($episodes as $partNumber => $episodeTitle) {
            $isCompleted = has_completed_part($partNumber); // Check if the corresponding story part is completed
            $buttonClass = $isCompleted ? "is-success" : "is-warning"; // Change button color based on completion status
            $buttonText = $isCompleted ? "Unlocked! Start Quiz" : "Locked, please complete part $partNumber of the story";
            $buttonState = $isCompleted ? "" : "disabled"; // Disable button if not completed
            $iconClass = $isCompleted ? "fas fa-check" : "fas fa-lock"; // Lock icon if locked, check icon if unlocked

            // Display each quiz with the corresponding story title
            echo <<<HTML
            <div class="column is-one-third">
                <div class="box has-text-centered">
                    <p class="title is-5">$episodeTitle</p>
                    <form action="episodeQuiz.php" method="GET">
                        <input type="hidden" name="episodeID" value="$partNumber">
                        <button class="button $buttonClass" $buttonState type="submit">
                            <span class="icon"><i class="$iconClass"></i></span>
                            <span>$buttonText</span>
                        </button>
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
