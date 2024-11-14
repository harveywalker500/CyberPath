<?php
// Include the functions file
require_once("functions.php");

session_start(); //Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

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

<div class="columns">
  <div class="column is-two-thirds">
    <div class="box">
        <?php foreach ($storyList as $story): // Iterate through each story item ?>
            <div>
                <p><?php echo htmlspecialchars($story['storyText']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
  </div>
  <div class="column is-one-third">
    <div class="box">
        <?php foreach ($storyList as $story): // Iterate through each quiz question item ?>
            <?php
            // Check if there's a question for this story
            if (isset($story['storyQuestion'])) {
                $question = $story['storyQuestion'];
                $answerA = $story['answerA'];
                $answerB = $story['answerB'];
                $answerC = $story['answerC'];
                
                // Display the question and answers as a form
                echo "<form action='submit_answer.php' method='POST'>";
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
                echo "<div class='notification is-warning'>No question available for this story.</div>";
            }
            ?>
        <?php endforeach; ?>
    </div>
  </div>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
