<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : 1; // Default to episode 1 if not set.

$hasPermission = userStoryPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    echo "<div class='notification is-danger'>You do not have permission to view this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

$dbConn = getConnection();
$sql = "
    SELECT s.*, e.episodeName
    FROM storyTable s
    JOIN episodesTable e ON s.episodeID = e.episodeID
    WHERE s.episodeID = :episodeID
";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$storyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($storyList)) {
    echo "<div class='notification is-warning'>No quiz questions found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

$episodeName = $storyList[0]['episodeName'];

?>

<!-- FRONTEND: HTML and JavaScript -->
<div class="columns">
  <div class="column is-two-thirds">
    <div class="box" id="storyText">
        <?php
        echo "<div>";
        echo "<p>" . htmlspecialchars($storyList[0]['storyText']) . "</p>";
        echo "</div>";
        ?>
    </div>
  </div>
  <div class="column is-one-third">
    <div class="box" id="quizBox">
        <?php
        // Check if there's a question for this episode
        if (isset($storyList[0]['storyQuestion'])) {
            $question = $storyList[0]['storyQuestion'];
            $answerA = $storyList[0]['answerA'];
            $answerB = $storyList[0]['answerB'];
            $answerC = $storyList[0]['answerC'];
            
            // Display the question and answers as a form
            echo "<form id='quizForm' action='#' method='POST'>";
            echo "<div class='field'>";
            echo "<label class='label'>$question</label>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='A'> $answerA";
            echo "</label>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='B'> $answerB";
            echo "</label>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='C'> $answerC";
            echo "</label>";
            echo "</div>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<input type='hidden' name='episodeID' value='$episodeID'>";
            echo "<button class='button is-primary' type='submit'>Submit Answer</button>";
            echo "</div>";
            echo "</form>";
        } else {
            echo "<div class='notification is-warning'>No question available for this episode.</div>";
        }
        ?>
    </div>
  </div>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>

<!-- FRONTEND: JavaScript (AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    var currentEpisode = <?php echo $episodeID; ?>; // Current episode ID

    // Handle form submission via AJAX
    $('#quizForm').submit(function(event) {
        event.preventDefault(); // Prevent the form from submitting the usual way
        
        var selectedAnswer = $("input[name='answer']:checked").val(); // Get the selected answer
        var episodeID = $("input[name='episodeID']").val(); // Get the episode ID
        
        if (!selectedAnswer) {
            alert("Please select an answer.");
            return;
        }

        // Send the answer to the server via AJAX
        $.ajax({
            url: '', // The same page
            method: 'POST',
            data: {
                episodeID: episodeID,
                answer: selectedAnswer
            },
            success: function(response) {
                // If answer is correct, update the story content
                if (response.success) {
                    // Update the story text and quiz question with the next part
                    $('#storyText').html("<p>" + response.nextStoryText + "</p>");
                    $('#quizBox').html(response.nextQuiz);

                    // If it's the last part, redirect to completion.php
                    if (response.isLastPart) {
                        window.location.href = "completion.php";
                    }
                } else {
                    alert("Incorrect answer. Please try again.");
                }
            },
            error: function() {
                alert("There was an error submitting your answer. Please try again.");
            }
        });
    });
});
</script>

<?php
// BACKEND PHP (Logic Handling Answer and Next Story)
if (isset($_POST['episodeID']) && isset($_POST['answer'])) {
    $episodeID = $_POST['episodeID'];
    $userAnswer = $_POST['answer'];

    // Get the correct answer for the given episode from the database
    $dbConn = getConnection();
    $sql = "
        SELECT s.*, e.episodeName, s.correctAnswer
        FROM storyTable s
        JOIN episodesTable e ON s.episodeID = e.episodeID
        WHERE s.episodeID = :episodeID
    ";
    $stmt = $dbConn->prepare($sql);
    $stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
    $stmt->execute();
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$story) {
        echo json_encode(['success' => false, 'message' => 'Story not found.']);
        exit;
    }

    // Check if the user's answer is correct
    if ($userAnswer === $story['correctAnswer']) {
        // Increment episodeID to go to the next part
        $nextEpisodeID = $episodeID + 1;

        // Get the next story part
        $sqlNext = "SELECT * FROM storyTable WHERE episodeID = :episodeID";
        $stmtNext = $dbConn->prepare($sqlNext);
        $stmtNext->bindParam(':episodeID', $nextEpisodeID, PDO::PARAM_INT);
        $stmtNext->execute();
        $nextStory = $stmtNext->fetch(PDO::FETCH_ASSOC);

        if ($nextStory) {
            // Return the next part of the story and quiz
            echo json_encode([
                'success' => true,
                'nextStoryText' => htmlspecialchars($nextStory['storyText']),
                'nextQuiz' => "
                    <form id='quizForm' action='#' method='POST'>
                        <div class='field'>
                            <label class='label'>" . htmlspecialchars($nextStory['storyQuestion']) . "</label>
                            <div class='control'>
                                <label class='radio'>
                                    <input type='radio' name='answer' value='A'> " . htmlspecialchars($nextStory['answerA']) . "
                                </label>
                            </div>
                            <div class='control'>
                                <label class='radio'>
                                    <input type='radio' name='answer' value='B'> " . htmlspecialchars($nextStory['answerB']) . "
                                </label>
                            </div>
                            <div class='control'>
                                <label class='radio'>
                                    <input type='radio' name='answer' value='C'> " . htmlspecialchars($nextStory['answerC']) . "
                                </label>
                            </div>
                        </div>
                        <input type='hidden' name='episodeID' value='$nextEpisodeID'>
                        <button class='button is-primary' type='submit'>Submit Answer</button>
                    </form>
                ",
                'isLastPart' => false // Change this if it's the last part of the story
            ]);
        } else {
            echo json_encode(['success' => true, 'isLastPart' => true]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>
