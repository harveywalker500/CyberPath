<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

echo makePageStart("CyberPath | Quiz");
echo makeNavMenu("CyberPath");

// Use $_POST to retrieve episodeID from the form submission
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

// Debugging check
if ($episodeID === null) {
    echo "<div class='notification is-danger'>Error: episodeID is not set.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd(); 
    exit; // Exit if no episodeID is provided
}

$hasPermission = userQuizPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

// Query for the quiz questions
$dbConn = getConnection();
$sql = "
    SELECT q.*, e.episodeName
    FROM questionTable q
    JOIN episodesTable e ON q.episodeID = e.episodeID
    WHERE q.episodeID = :episodeID
";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($quizlist)) {
    echo "<div class='notification is-warning'>No quiz questions found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}
$episodeName = $quizlist[0]['episodeName']; 
?>

<div class="container">
<h1 class="title has-text-centered">Quiz for <?php echo htmlspecialchars($episodeName); ?></h1>
    <form action="quizResults.php" method="POST">
        <?php
        // Loop through each question in the quiz and create the form inputs
        foreach ($quizlist as $row) {
            echo "<div class='box'>";
            echo "<h4 class='subtitle'>" . htmlspecialchars($row['questionText']) . "</h4>";

            // Display each answer option with radio buttons
            echo "<div class='field'>";
            echo "<input type='radio' id='answerA_" . $row['questionID'] . "' name='question_" . $row['questionID'] . "' value='" . htmlspecialchars($row['answerA']) . "' class='is-checkradio' required>";
            echo "<label for='answerA_" . $row['questionID'] . "'>" . htmlspecialchars($row['answerA']) . "</label>";
            echo "</div>";

            echo "<div class='field'>";
            echo "<input type='radio' id='answerB_" . $row['questionID'] . "' name='question_" . $row['questionID'] . "' value='" . htmlspecialchars($row['answerB']) . "' class='is-checkradio' required>";
            echo "<label for='answerB_" . $row['questionID'] . "'>" . htmlspecialchars($row['answerB']) . "</label>";
            echo "</div>";

            echo "<div class='field'>";
            echo "<input type='radio' id='answerC_" . $row['questionID'] . "' name='question_" . $row['questionID'] . "' value='" . htmlspecialchars($row['answerC']) . "' class='is-checkradio' required>";
            echo "<label for='answerC_" . $row['questionID'] . "'>" . htmlspecialchars($row['answerC']) . "</label>";
            echo "</div>";

            echo "<div class='field'>";
            echo "<input type='radio' id='answerD_" . $row['questionID'] . "' name='question_" . $row['questionID'] . "' value='" . htmlspecialchars($row['answerD']) . "' class='is-checkradio' required>";
            echo "<label for='answerD_" . $row['questionID'] . "'>" . htmlspecialchars($row['answerD']) . "</label>";
            echo "</div>";

            echo "</div>"; // Close the box for the question
        }
        ?>

        <!-- Hidden input to pass the episodeID -->
        <input type="hidden" name="episodeID" value="<?php echo htmlspecialchars($episodeID); ?>">

        <div class="field has-text-centered">
            <button type="submit" class="button is-primary">Submit my answers</button>
        </div>
    </form>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
