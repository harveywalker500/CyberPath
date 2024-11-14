<?php
// Include the functions file
require_once("functions.php");

session_start(); // Starts the session
loggedIn(); // Ensures the user is logged in before loading the page

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

// Fetch the story list for the episode
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

// Initialize or use the current index in session
if (!isset($_SESSION['currentIndex'])) {
    $_SESSION['currentIndex'] = 0;
}

// Get the current story and question based on the current index
$currentStory = $storyList[$_SESSION['currentIndex']];
?>

<div id="content">
    <div class="columns">
        <div class="column is-two-thirds">
            <div class="box">
                <div id="storyText">
                    <p><?php echo htmlspecialchars($currentStory['storyText']); ?></p>
                </div>
            </div>
        </div>
        <div class="column is-one-third">
            <div class="box">
                <form id="quizForm" action="next_question.php" method="POST">
                    <div class="field" id="questionField">
                        <label class="label"><?php echo htmlspecialchars($currentStory['storyQuestion']); ?></label>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="A" required> <?php echo htmlspecialchars($currentStory['answerA']); ?>
                            </label>
                        </div>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="B" required> <?php echo htmlspecialchars($currentStory['answerB']); ?>
                            </label>
                        </div>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="C" required> <?php echo htmlspecialchars($currentStory['answerC']); ?>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="episodeID" value="<?php echo $episodeID; ?>">
                    <button class="button is-primary" type="submit">Submit Answer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#quizForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            $.ajax({
                url: 'next_question.php', // The PHP endpoint
                type: 'POST',
                data: $(this).serialize(), // Send form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.completed) {
                        // If all questions are completed, show completion message
                        $('#content').html('<div class="notification is-success">You have completed all questions in this episode!</div>');
                    } else {
                        // Update the story and question text
                        $('#storyText').html('<p>' + response.storyText + '</p>');
                        $('#questionField').html(
                            '<label class="label">' + response.storyQuestion + '</label>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="A" required> ' + response.answerA + '</label></div>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="B" required> ' + response.answerB + '</label></div>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="C" required> ' + response.answerC + '</label></div>'
                        );
                    }
                }
            });
        });
    });
</script>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
