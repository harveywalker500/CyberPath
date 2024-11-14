<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session
loggedIn(); // Ensures the user is logged in before loading the page

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

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

// Fetch the story list for the episode
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

// Initialize or update the current index in session
if (!isset($_SESSION['currentIndex'])) {
    $_SESSION['currentIndex'] = 0;
} elseif (isset($_POST['next'])) {
    $_SESSION['currentIndex']++; // Increment the index when 'next' is posted
}

// Check if the current index is within bounds of storyList
if ($_SESSION['currentIndex'] >= count($storyList)) {
    echo "<div class='notification is-success'>You have completed all questions in this episode!</div>";
    unset($_SESSION['currentIndex']); // Reset index or redirect if needed
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

// Get the current story and question based on the current index
$currentStory = $storyList[$_SESSION['currentIndex']];
?>

<div class="columns">
  <div class="column is-two-thirds">
    <div class="box">
        <div>
            <p><?php echo htmlspecialchars($currentStory['storyText']); ?></p>
        </div>
    </div>
  </div>
  <div class="column is-one-third">
    <div class="box">
        <?php
        // Check if there's a question for this story
        if (isset($currentStory['storyQuestion'])) {
            $question = $currentStory['storyQuestion'];
            $answerA = $currentStory['answerA'];
            $answerB = $currentStory['answerB'];
            $answerC = $currentStory['answerC'];
            
            // Display the question and answers as a form
            echo "<form action='' method='POST'>";
            echo "<div class='field'>";
            echo "<label class='label'>$question</label>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='A' required> $answerA";
            echo "</label>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='B' required> $answerB";
            echo "</label>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='answer' value='C' required> $answerC";
            echo "</label>";
            echo "</div>";
            echo "</div>";

            echo "<div class='control'>";
            echo "<input type='hidden' name='episodeID' value='$episodeID'>";
            echo "<button class='button is-primary' type='submit' name='next'>Submit Answer</button>";
            echo "</div>";
            echo "</form>";
        } else {
            echo "<div class='notification is-warning'>No question available for this story.</div>";
        }
        ?>
    </div>
  </div>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
