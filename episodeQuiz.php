<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

echo makePageStart("CyberPath | Quiz");
echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "quizSelection.php" => "Quiz Selection", "leaderboard.php"  => "Leaderboard"));

// Use $_POST to retrieve episodeID from the form submission
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

// Debugging check
if ($episodeID === null) {
    echo "Error: episodeID is not set.";
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
$sql = "SELECT * FROM questionTable WHERE episodeID = :episodeID";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($quizlist)) {
    echo "No quiz questions found for this episode.";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

$quizData = json_encode([
    'quizTitle' => "Episode $episodeID Quiz",
    'questions' => $quizlist
]);

?>

<div id='quiz-root"></div>

<script src ="quizComponent.bundle.js"></script>

<script>
    const quizData = <?php echo $quizData; ?>;
</script>
    

<?php

echo makeFooter("This is the footer");
echo makePageEnd();
?>
