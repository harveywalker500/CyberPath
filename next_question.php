<?php
session_start();
require_once("functions.php");

if (!isset($_POST['episodeID'])) {
    echo json_encode(["error" => "Episode ID not set"]);
    exit;
}

$episodeID = $_POST['episodeID'];
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

// Increment index and check completion
if (!isset($_SESSION['currentIndex'])) {
    $_SESSION['currentIndex'] = 0;
} else {
    $_SESSION['currentIndex']++;
}

if ($_SESSION['currentIndex'] >= count($storyList)) {
    echo json_encode(["completed" => true]);
    unset($_SESSION['currentIndex']);
    exit;
}

$currentStory = $storyList[$_SESSION['currentIndex']];
echo json_encode([
    "completed" => false,
    "storyText" => htmlspecialchars($currentStory['storyText']),
    "storyQuestion" => htmlspecialchars($currentStory['storyQuestion']),
    "answerA" => htmlspecialchars($currentStory['answerA']),
    "answerB" => htmlspecialchars($currentStory['answerB']),
    "answerC" => htmlspecialchars($currentStory['answerC']),
]);
