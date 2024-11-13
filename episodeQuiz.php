<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

// Use $_POST to retrieve episodeID from the form submission
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

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
    echo "<h4>" . htmlspecialchars($row['question']) . "</h4>";
    
    // Display each answer option with radio buttons
    echo "<input type='radio' name='" . htmlspecialchars($row['question']) . "' value='" . htmlspecialchars($row['answera']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answera']) . "'>" . htmlspecialchars($row['answera']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['question']) . "' value='" . htmlspecialchars($row['answerb']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerb']) . "'>" . htmlspecialchars($row['answerb']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['question']) . "' value='" . htmlspecialchars($row['answerc']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerc']) . "'>" . htmlspecialchars($row['answerc']) . "</label><br>";
    
    echo "<input type='radio' name='" . htmlspecialchars($row['question']) . "' value='" . htmlspecialchars($row['answerd']) . "'>";
    echo "<label for='" . htmlspecialchars($row['answerd']) . "'>" . htmlspecialchars($row['answerd']) . "</label><br>";
}

// Use htmlspecialchars on $episodeID in case it needs to be passed forward
echo "<input type='hidden' name='quiz_name' value='" . htmlspecialchars($episodeID) . "'>";
echo "<br><input type='submit' value='Submit my answers'><br>";
echo "</form>";

echo makeFooter("This is the footer");
echo makePageEnd();
?>
