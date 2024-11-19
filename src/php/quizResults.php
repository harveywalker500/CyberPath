<?php
// Include the functions file
require_once("functions.php");
session_start(); //Starts the session.
loggedIn();

echo makePageStart("CyberPath", "../../css/stylesheet.css");
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
    SELECT q.*, e.episodeName
    FROM questionTable q
    JOIN episodesTable e ON q.episodeID = e.episodeID
    WHERE q.episodeID = :episodeID
";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1 class='title has-text-centered'>Results for ". $quizlist[0]['episodeName'] ."</h1>";

if (empty($quizlist)) {
    echo "<div class='notification is-warning'>No results found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

$questionCount = 1;
foreach ($quizlist as $question) {
    $questionID = $question['questionID'];
    $correctAnswer = $question['correctAnswer'];
    $questionText = $question['questionText'];

    $answers = [
        'a' => $question['answerA'],
        'b' => $question['answerB'],
        'c' => $question['answerC'],
        'd' => $question['answerD']
    ];

    $correctAnswerText = isset($answers[$correctAnswer]) ? $answers[$correctAnswer] : '';

    if (isset($userAnswers[$questionID])) {
        $userAnswer = $userAnswers[$questionID];

        if($userAnswer == $correctAnswer){
            $correctAnswers++;
            echo "<div class='notification is-success'>Question {$questionCount} is correct!</div>";
            echo "<div><strong>Question:</strong> {$questionText}</div>";
            echo "<div><strong>Correct Answer:</strong> {$correctAnswerText}</div>";
            $questionCount++;
        } else {
            echo "<div class='notification is-danger'>Question {$questionCount} is incorrect. The correct answer was {$correctAnswerText}.</div>";
            echo "<div><strong>Question:</strong> {$questionText}</div>";
            echo "<div><strong>Correct Answer:</strong> {$correctAnswerText}</div>";
            $questionCount++;;
        }
    } else {
        echo "<div class='notification is-warning'>No answer provided for question {$questionID}.</div>";
    }
}

// Determine the appropriate message based on the score
if ($correctAnswers > 3) {
    $message = "<strong>Well Done</strong> " . $_SESSION['username'] . ", you got {$correctAnswers} out of " . count($quizlist) . " questions correct.";
} elseif ($correctAnswers >= 1 && $correctAnswers <= 3) {
    $message = "<strong>Unlucky</strong> " . $_SESSION['username'] . ", you got {$correctAnswers} out of " . count($quizlist) . " questions correct. Better luck next time!";
} else {
    $message = "<strong>Ouch!</strong> " . $_SESSION['username'] . ", you got {$correctAnswers} out of " . count($quizlist) . " questions correct. Don't worry, you can do better next time!";
}

// Display the message
echo "<div class='notification is-info'>{$message}</div>";


$quizColumn = 'quiz' . $episodeID . 'Score';  // e.g., quiz1Score, quiz2Score, etc.
$updateScoreSql = "
    UPDATE leaderboardTable
    SET {$quizColumn} = :score
    WHERE userID = :userID AND {$quizColumn} < :score
";
$updateStmt = $dbConn->prepare($updateScoreSql);
$updateStmt->bindParam(':score', $correctAnswers, PDO::PARAM_INT);
$updateStmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
$updateStmt->execute();

echo makeFooter();
echo makePageEnd();
?>