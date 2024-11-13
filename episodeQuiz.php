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

<div id="quiz-root"></div>

<script>
    // Get the quiz data from PHP
    const quizData = <?php echo $quizData; ?>; // This assumes the PHP data is being passed correctly.

    // Ensure React renders the component to the 'quiz-root' div
    const rootElement = document.getElementById('quiz-root');
    if (rootElement) {
        const root = ReactDOM.createRoot(rootElement); // React 18+ syntax, for older versions it would be ReactDOM.render()
        root.render(<quizComponent quizData={quizData} />);
    } else {
        console.error("No element with id 'quiz-root' found.");
    }
</script>

    

<?php

echo makeFooter("This is the footer");
echo makePageEnd();
?>
