<?php
session_start();
require_once("functions.php");

if (!isset($_POST['episodeID']) || !isset($_POST['answer'])) {
    echo json_encode(["error" => "Episode ID or answer not set"]);
    exit;
}

$episodeID = $_POST['episodeID'];
$selectedAnswer = $_POST['answer'];
$userID = $_SESSION['userID']; // Assuming userID is stored in session

// Fetch the story list for the episode from the database
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

// Initialize or increment the question index in the session
if (!isset($_SESSION['currentIndex'])) {
    $_SESSION['currentIndex'] = 0;
}

$currentStory = $storyList[$_SESSION['currentIndex']];

// Check if the answer is correct
if ($selectedAnswer === $currentStory['correctAnswer']) {
    // Increment index for the next question if the answer is correct
    $_SESSION['currentIndex']++;

    // Check if there are no more questions left
    if ($_SESSION['currentIndex'] >= count($storyList)) {
        // All questions are completed; update the userProgressTable
        $updateSql = "
            UPDATE userProgressTable
            SET storyCompleted = :episodeID
            WHERE userID = :userID
        ";
        $updateStmt = $dbConn->prepare($updateSql);
        $updateStmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
        $updateStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $updateStmt->execute();

        // Clear the session index and indicate quiz completion
        unset($_SESSION['currentIndex']);
        echo json_encode([
            "completed" => true,
            "message" => "You have completed all questions in this episode!"
        ]);
        exit;
    }

    // Load the next question
    $nextStory = $storyList[$_SESSION['currentIndex']];
    echo json_encode([
        "completed" => false,
        "correct" => true,
        "message" => "Correct answer! Moving to the next question.",
        "storyText" => htmlspecialchars($nextStory['storyText']),
        "storyQuestion" => htmlspecialchars($nextStory['storyQuestion']),
        "answerA" => htmlspecialchars($nextStory['answerA']),
        "answerB" => htmlspecialchars($nextStory['answerB']),
        "answerC" => htmlspecialchars($nextStory['answerC']),
    ]);
} else {
    // Respond with an incorrect answer message without advancing
    echo json_encode([
        "completed" => false,
        "correct" => false,
        "message" => "Incorrect answer! Please try again."
    ]);
}
