<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

// Use $_POST to retrieve episodeID from the form submission
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

// Debugging check
if ($episodeID === null) {
    echo "Error: episodeID is not set.";
    exit; // Exit if no episodeID is provided
}

$hasPermission = userQuizPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    exit;
}

echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "quizSelection.php" => "Quiz Selection", "leaderboard.php" => "Leaderboard"));

// Query for the quiz questions
$dbConn = getConnection();
$sql = "SELECT * FROM questionTable WHERE episodeID = :episodeID";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<form action='quizResults.php'>";

foreach ($quizlist as $row) {
    echo "<h4>" . htmlspecialchars($row['questionText']) . "</h4>";
    
    // Display each answer option with radio buttons
    echo "<input type='radio' name='" . htmlspecialchars($row['questionText']) . "' value='" . htmlspecialchars($row['answerA']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerA']) . "'>" . htmlspecialchars($row['answerA']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['questionText']) . "' value='" . htmlspecialchars($row['answerB']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerB']) . "'>" . htmlspecialchars($row['answerA']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['questionText']) . "' value='" . htmlspecialchars($row['answerC']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerD']) . "'>" . htmlspecialchars($row['answerC']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['questionText']) . "' value='" . htmlspecialchars($row['answerD']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerD']) . "'>" . htmlspecialchars($row['answerD']) . "</label><br>";
}

// Use htmlspecialchars on $episodeID in case it needs to be passed forward
echo "<input type='hidden' name='quiz_name' value='" . htmlspecialchars($episodeID) . "'>";
echo "<br><input type='submit' value='Submit my answers'><br>";
echo "</form>";

echo makeFooter("This is the footer");
echo makePageEnd();
?>
