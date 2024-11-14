<?php
require_once("functions.php");
session_start();

$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;
$selectedAnswer = isset($_POST['answer']) ? $_POST['answer'] : null;

if ($episodeID === null || $selectedAnswer === null) {
    header('Location: index.php');
    exit;
}

// Get database connection
$dbConn = getConnection();

// Query to get the correct answer for the current story
$sql = "SELECT correctAnswer FROM storyTable WHERE episodeID = :episodeID";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the answer is correct
if ($result && $selectedAnswer === $result['correctAnswer']) {
    // Correct answer, proceed to next story
    
    // Get the next story in the sequence
    $nextStorySql = "
        SELECT s.episodeID
        FROM storyTable s
        WHERE s.episodeID > :episodeID
        ORDER BY s.episodeID ASC
        LIMIT 1
    ";
    $nextStoryStmt = $dbConn->prepare($nextStorySql);
    $nextStoryStmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
    $nextStoryStmt->execute();
    $nextStory = $nextStoryStmt->fetch(PDO::FETCH_ASSOC);

    // If there's a next story, redirect to it
    if ($nextStory) {
        header("Location: story_page.php?episodeID=" . $nextStory['episodeID']);
    } else {
        // No more stories, redirect to a completion page or similar
        header("Location: completion.php");
    }
    exit;

} else {
    // Incorrect answer, show an error message or redirect back to the question
    header("Location: story_page.php?episodeID=$episodeID&error=incorrect");
    exit;
}
?>
