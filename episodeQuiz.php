<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.
$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;
$hasPermission =  userQuizPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    exit;
}
echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "quizSelection.php" => "Quiz Selection", "leaderboard.php"  => "Leaderboard"));

//I now need to create a form. I will start the form here and then within the form run a loop to output the results of the query
$dbConn = getConnection();
$sql = "SELECT * FROM questionTable WHERE episodeID = :episodeID";
$stmt = $dbConn->prepare($sql);
$stmt->bindParam(':episodeID', $episodeID, PDO::PARAM_INT);
$stmt->execute();
$quizlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<form action = 'quizResults.php'>";

//now that the form has started I need to output the question and answers from each row of the query
//The questions will be displayed in a heading tag and the answers will be labels on radio buttons.
//Each set of radio buttons must have the same name (but be different for each question) so that only one option can be chosen in a single question

while($row = mysqli_fetch_array($quizlist)){
	echo "<h4>".$row['question']."</h4>";
	
	echo "<input type='radio' name='".$row['question']."' value='".$row['answera']."'>";
	echo "<label for='".$row['answera']."'>".$row['answera']."</label><br>";
	
	echo "<input type='radio' name='".$row['question']."' value='".$row['answerb']."'>";
	echo "<label for='".$row['answerb']."'>".$row['answerb']."</label><br>";
	
	echo "<input type='radio' name='".$row['question']."' value='".$row['answerc']."'>";
	echo "<label for='".$row['answerc']."'>".$row['answerc']."</label><br>";
	
	echo "<input type='radio' name='".$row['question']."' value='".$row['answerd']."'>";
	echo "<label for='".$row['answerd']."'>".$row['answerd']."</label><br>";
    
};	
echo "<input type='hidden' name='quiz_name' value='".$_GET['quiz']."'>";

echo "<br><input type='submit' value='Submit my answers'><br>";
echo "</form>";


echo makeFooter("This is the footer");
echo makePageEnd();
?>