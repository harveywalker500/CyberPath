<?php
// Include the functions file
require_once("functions.php");

session_start(); //Starts the session.
loggedIn(); // Ensures the user is logged in before loading the page.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

$episodeID = isset($_POST['episodeID']) ? $_POST['episodeID'] : null;

if ($episodeID === null) {
    echo "<div class='notification is-danger'>Error: episodeID is not set.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd(); 
    exit; // Exit if no episodeID is provided
}

$hasPermission = userStoryPermission($_SESSION['userID'], $episodeID);

if (!$hasPermission) {
    header('Location: index.php');
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}

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

if (empty($storyList)) {
    echo "<div class='notification is-warning'>No quiz questions found for this episode.</div>";
    echo makeFooter("This is the footer");
    echo makePageEnd();
    exit;
}
$episodeName = $storyList[0]['episodeName']; 
?>

<div class="columns">
  <div class="column is-two-thirds">
    <div class="box">
      <!-- Content for the first box -->
      Box 1 (2/3 width)
    </div>
  </div>
  <div class="column is-one-third">
    <div class="box">
      <!-- Content for the second box -->
      Box 2 (1/3 width)
    </div>
  </div>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>

