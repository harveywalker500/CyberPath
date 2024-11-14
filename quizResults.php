<?php
// Include the functions file
require_once("functions.php");
session_start(); //Starts the session.
loggedIn();

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;
if ($episodeID === null) {
    echo "<div class='notification is-danger'>Error: episodeID is not set.</div>";
    exit;
}
$hasPermission = userQuizPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    exit;
}

$userAnswers = [];
foreach ($_POST as $key => $value) {
    // Only process keys that start with 'question_'
    if (strpos($key, 'question_') === 0) {
        // Extract question ID by removing the 'question_' prefix
        $questionID = str_replace('question_', '', $key);
        $userAnswers[$questionID] = $value; // Store answer by question ID
    }
}
$correctAnswers = 0;

$dbConn = getConnection();
$sql = "
    SELECT q.questionID, q.correctAnswer, q.questionText, e.episodeName
    FROM questionTable q
    JOIN episodesTable e ON q.episodeID = e.episodeID
    WHERE q.episodeID = :episodeID
";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>". $quizlist['episodeName'] ."</h1>";

if (empty($quizlist)) {
    echo "<div class='notification is-warning'>No results found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

foreach ($quizlist as $question) {
    $questionID = $question['questionID'];
    $correctAnswer = $question['correctAnswer'];
    $questionCount = 1;
    if (isset($userAnswers[$questionID])) {
        $userAnswer = $userAnswers[$questionID];

        if($userAnswer == $correctAnswer){
            $correctAnswer++;
            echo "<div class='notification is-success'>Question {$questionCount} is correct!</div>";
            $questionCount++;
        } else {
            echo "<div class='notification is-danger'>Question {$questionCount} is incorrect. The correct answer was {$correctAnswer}.</div>";
            $questionCount++;
        }
    } else {
        echo "<div class='notification is-warning'>No answer provided for question {$questionID}.</div>";
    }
}
echo "<div class='notification is-info'>You got {$correctAnswers} out of " . count($quizlist) . " questions correct.</div>";

echo makeFooter("This is the footer");
echo makePageEnd();
?>