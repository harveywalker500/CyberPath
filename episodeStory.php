<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// Get episode ID from POST or set it to null if not provided
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

if ($episodeID === null) {
    echo "<div class='notification is-danger'>Error: episodeID is not set.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd(); 
    exit; // Exit if no episodeID is provided
}

$hasPermission = userStoryPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

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
$storyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($storyList)) {
    echo "<div class='notification is-warning'>No quiz questions found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

$episodeName = $storyList[0]['episodeName']; 
?>

<div class="columns">
  <div class="column is-two-thirds">
    <div class="box">
        <div>
            <p><?php echo htmlspecialchars($storyList[0]['storyText']); ?></p>
        </div>
    </div>
  </div>
  <div class="column is-one-third">
    <div class="box">

        <?php
        // Check if there's a question for this episode
        if (isset($storyList[0]['storyQuestion'])) {
            $question = $storyList[0]['storyQuestion'];
            $answerA = $storyList[0]['answerA'];
            $answerB = $storyList[0]['answerB'];
            $answerC = $storyList[0]['answerC'];
            
            // Display the question and answers as a form
            echo "<form id='quizForm' method='POST'>";
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

<!-- Place your JavaScript and AJAX inside the HTML document -->
<script>
$(document).ready(function() {
    $('#quizForm').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = $(this).serialize(); // Serialize the form data

        $.ajax({
            url: '', // Submit to the same page
            type: 'POST',
            data: formData,
            success: function(response) {
                var data = JSON.parse(response);
                
                if (data.success) {
                    // If the answer is correct, show the next story part
                    if (!data.isLastPart) {
                        // Display the next story and quiz question
                        $('div.columns .column.is-two-thirds').html("<div class='box'><p>" + data.nextStoryText + "</p></div>");
                        $('div.columns .column.is-one-third').html(data.nextQuiz);
                    } else {
                        // If it's the last part, show completion message
                        $('div.columns .column.is-two-thirds').html("<div class='box'><p>Congratulations! You've completed the story!</p></div>");
                        $('div.columns .column.is-one-third').html("");
                    }
                } else {
                    // If the answer was incorrect, notify the user
                    alert('Incorrect answer. Please try again.');
                }
            }
        });
    });
});
</script>

<?php
// Backend logic for checking the submitted answer
if (isset($_POST['episodeID']) && isset($_POST['answer'])) {
    $episodeID = $_POST['episodeID']; // Get the episode ID
    $userAnswer = $_POST['answer'];   // Get the user's selected answer (A, B, or C)

    // Fetch the correct answer from the database
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

    // Ensure the correct answer is trimmed of whitespace
    $correctAnswer = trim($story['correctAnswer']);
    $nextEpisodeID = $episodeID + 1; // Increment the episode ID to get the next part of the story

    // Compare the user's answer to the correct answer
    if ($userAnswer === $correctAnswer) {
        // Correct answer: Get the next story part
        $sqlNext = "SELECT * FROM storyTable WHERE episodeID = :episodeID";
        $stmtNext = $dbConn->prepare($sqlNext);
        $stmtNext->bindParam(':episodeID', $nextEpisodeID, PDO::PARAM_INT);
        $stmtNext->execute();
        $nextStory = $stmtNext->fetch(PDO::FETCH_ASSOC);

        // If there is a next episode, return the updated story and quiz
        if ($nextStory) {
            echo json_encode([
                'success' => true,
                'nextStoryText' => htmlspecialchars($nextStory['storyText']),
                'nextQuiz' => "
                    <form id='quizForm' method='POST'>
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
                'isLastPart' => false // This flag can be set to true if it's the final episode part
            ]);
        } else {
            // No more episodes, end of the story
            echo json_encode([
                'success' => true,
                'isLastPart' => true
            ]);
        }
    } else {
        // Incorrect answer
        echo json_encode(['success' => false]);
    }
    exit; // Terminate the script after responding
}

