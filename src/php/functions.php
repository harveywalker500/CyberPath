<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getConnection(){ //function to get the connection to the database, allows users to query
    try{
        $connection = new PDO("mysql:host=nuwebspace_db; dbname=w22009720","w22009720", "Bwjddnxa");//PDO is data abstraction layer
        //$connection = new PDO('mysql:host=localhost; dbname=test12','root','');
        $connection ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//sets attributes on PDO connection. Turns errors and exception reporting on.
        return $connection;
        
    }catch(Exception $e) {
        throw new Exception("Connection error".$e->getMessage(), 0 ,$e);
    }
}

function makePageStart($title, $stylesheet) 
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>$title</title>
        <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css" rel="stylesheet">
        <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery CDN -->
		<script src="../js/dev2.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script> <!-- DataTables CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="$stylesheet">
		<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">  
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
HTML;
}


function logStoryCompletion($userID, $storyID, $startTime, $endTime) {
    $db = getconnection();

    $query = "
        INSERT INTO storyCompletionLog (userID, storyID, startTime, endTime)
        VALUES (:userID, :storyID, :startTime, :endTime)
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':userID' => $userID,
        ':storyID' => $storyID,
        ':startTime' => $startTime,
        ':endTime' => $endTime
    ]);
}


function updateOrganisationMetric($organisationID, $metricType, $metricValue) {
    $db = getconnection();

    $query = "
        INSERT INTO organisationMetrics (organisationID, metricType, metricValue)
        VALUES (:organisationID, :metricType, :metricValue)
        ON DUPLICATE KEY UPDATE
        metricValue = :metricValue, recordedAt = CURRENT_TIMESTAMP
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':organisationID' => $organisationID,
        ':metricType' => $metricType,
        ':metricValue' => $metricValue
    ]);
}


function updateEmployeeStatus($userID, $isActive) {
    $db = getconnection();

    $query = "
        INSERT INTO employeeStatus (userID, isActive)
        VALUES (:userID, :isActive)
        ON DUPLICATE KEY UPDATE
        isActive = :isActive, updatedAt = CURRENT_TIMESTAMP
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':userID' => $userID,
        ':isActive' => $isActive
    ]);
}


function logEmployeeActivity($userID, $activityType) {
    $db = getconnection();

    $query = "
        INSERT INTO employeeActivityLog (userID, activityDate, activityType)
        VALUES (:userID, NOW(), :activityType)
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':userID' => $userID,
        ':activityType' => $activityType
    ]);
}


function updateSystemMetric($metricType, $metricValue) {
    $db = getconnection();

    $query = "
        INSERT INTO systemMetrics (metricType, metricValue)
        VALUES (:metricType, :metricValue)
        ON DUPLICATE KEY UPDATE
        metricValue = :metricValue, recordedAt = CURRENT_TIMESTAMP
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':metricType' => $metricType,
        ':metricValue' => $metricValue
    ]);
}


function show_errors($errors) {//function to show errors, parameter should be array of errors or empty
    echo "<h1 class='error-heading'>Errors</h1>\n";
    $output = "";
    foreach ($errors as $error) {
        $output .= "<p class='error-message'>$error</p>\n";//Concatenates each error into an error message and displays on screen.
    }
    return $output;
}

function getEpisodes() {
    try {
        // Get database connection
        $connection = getConnection();
        
        // Query to retrieve episodes
        $sql = "SELECT episodeID, episodeName FROM episodesTable ORDER BY episodeID";
        $stmt = $connection->query($sql);

        // Fetch episodes into an associative array
        $episodes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $episodes[$row['episodeID']] = $row['episodeName'];
        }

        return $episodes;
    } catch (Exception $e) {
        throw new Exception("Error fetching episodes: " . $e->getMessage(), 0, $e);
    }
}

function validate_login() {
    $input = array();
    $errors = array();
    $input['username'] = $_POST['username'] ?? '';
    $input['password'] = $_POST['password'] ?? '';
    $input['username'] = trim($input['username']);
    $input['password'] = trim($input['password']);

    try {
        $dbConn = getConnection();
        $sqlQuery = "SELECT userID, username, password FROM userTable WHERE username = :username";
        $stmt = $dbConn->prepare($sqlQuery);
        $stmt->execute(array(':username' => $input['username']));

        $user = $stmt->fetchObject();
        if ($user) {
            if (password_verify($input['password'], $user->password)) {
                $_SESSION['username'] = $user->username;
                $_SESSION['userID'] = $user->userID;  // Optionally store the user ID
                updateEmployeeStatus($user->id, 1); // Update user status to active
                logEmployeeActivity($user->id, 'Login'); // Log user login activity

            } else {
                $errors[] = "Login Details are incorrect.";
            }
        } else {
            $errors[] = "Login Details are incorrect.";
        }
    } catch (Exception $e) {
        echo "There was a problem: " . $e->getMessage();
    }
    return array($input, $errors);
}


function set_session($key, $value){//Function that takes two parameters to specify the session key and the corresponding value
    $_SESSION[$key]=$value;//Assigns provided value to the session variable thats identified by $key.
    return true;
}

function get_session($key){//Function designed to get the value of session variable based on key.
    if (isset($_SESSION[$key]))//Checks if session variable with the given key exists
    {
        return $_SESSION[$key];//If session exists, it will return the correct value.
    }
    return null;
}

function check_login(){//Checks if user is logged-in based on session variable "logged-in".
    if (get_session('logged-in')){//Checks if session variable with key "logged-in" exists and if it is true.
        return true;//Returns true if user is logged in
    }
    return false;//WIll return false if user isnt logged in

}

function loggedIn(){//Function that redirects users to loginform.php if they are not logged in.
    if (!check_login())
        {
            header('Location: ../../src/php/loginForm.php');//Redirects user
            exit();//Terminates script
        }
}



function logOut(){//Function to log out of account
    updateEmployeeStatus($_SESSION['userID'], 0);//Updates user status to inactive
    logEmployeeActivity($_SESSION['userID'], 'Logout');//Logs user logout activity
    $_SESSION = array();//Reset session array
    session_destroy();//Destroys current session
    header('Location: ../../index.php');//Redirects user to home page.
    exit();//Terminates script
}

function makeNavMenu($navMenuHeader) {
    // Check session variables for debugging

    // Predefined menu links
    $links = array(
        "../../index.php" => "Home",
        "../../src/php/storySelect.php" => "Story",
        "../../src/php/quizSelection.php" => "Quiz Selection",
        "../../src/php/Leaderboard-V2.php"  => "Leaderboard"
        );

    $output = <<<HTML
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="../../index.php">
                <img src="../../images/CyberPath.png" alt="Site Logo" style="width: 100px; height: 100px; max-height: none;">
            </a>
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasic">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasic" class="navbar-menu">
            <div class="navbar-start">
HTML;

    // Loop through the predefined links and create the menu items
    foreach ($links as $key => $value) {
        $output .= "<a class=\"navbar-item\" href=\"$key\">$value</a>\n";
    }

    // Check if the user is logged in
    if (check_login()) {
        $userID = get_session('userID');
        
        $userOrganisationID = getUserOrganisation($userID); // Get user's organisation ID

        if ($userOrganisationID) {
            $organisationDetails = getOrganisation($userOrganisationID); // Get organisation details
            
            if ($organisationDetails && $organisationDetails['teamLeaderID'] == $userID) {
                // Display "Manage Organisation" link if user is the team leader
                $output .= "<a class=\"navbar-item\" href='../../src/php/analytics.php?id=" . $userOrganisationID . "'>Analytics</a>\n";
            }
        }

        // Add organisation and logout option if logged in
        $output .= "<a class=\"navbar-item\" href='../../src/php/organisationPage.php'>Organisation</a>\n";
        $output .= "<a class=\"navbar-item\" href='../../src/php/logout.php'>Logout</a>\n";
    } else {
        // Add login and register options if not logged in
        $output .= "<a class=\"navbar-item\" href='../../src/php/loginForm.php'>Login</a>\n";
        $output .= "<a class=\"navbar-item\" href='../../src/php/register.php'>Register</a>\n";
    }

    $output .= <<<HTML
            </div>
        </div>
    </nav>
HTML;

    return $output;
}




function makeFooter() {
    return <<<HTML
    <footer class="footer">
        <div class="content has-text-centered">
            <p>&copy; 2024 CyberPath. All rights reserved.</p>
        </div>
    </footer>
HTML;
}


function makePageEnd() {
    return <<<HTML
    </div> <!-- End of content div -->
    <script src="../js/burger.js"></script>
    </body>
    </html>
HTML;
}

function makePageEndIndex() {
    return <<<HTML
    </div> <!-- End of content div -->
    <script src="../../src/js/burger.js"></script>
    </body>
    </html>
HTML;
}

function getUserProgress($userID) {
    try {
        $dbconn = getConnection();
        $sql = "SELECT * FROM userProgressTable WHERE userID = :userID";
        $stmt = $dbconn->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Error fetching user progress: " . $e->getMessage(), 0, $e);
    }
}

function getUserOrganisation($userID) {
    try {
        $dbConn = getConnection();
        $sql = "SELECT organisationID FROM userTable WHERE userID = :userID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);
        return $organisation ? $organisation['organisationID'] : null;
    } catch (Exception $e) {
        throw new Exception("Error fetching user organisation: " . $e->getMessage(), 0, $e);
    }
}

function getOrganisation($organisationID) {
    try {
        $dbConn = getConnection();
        $sql = "SELECT * FROM organisationTable WHERE organisationID = :organisationID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':organisationID' => $organisationID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Error fetching organisation: " . $e->getMessage(), 0, $e);
    }
}

function userQuizPermission($userID, $currentEpisode){
    $progress = getUserProgress($userID);
    if ($progress && isset($progress['storyCompleted'])) {
        if ($progress['storyCompleted'] >= $currentEpisode) {
            return true;
        }
    }
    return false;
}

function userStoryPermission($userID, $currentEpisode){
    $progress = getUserProgress($userID);
    if ($progress && isset($progress['quizCompleted'])) {
        if ($progress['quizCompleted'] >= $currentEpisode -1) {
            return true;
        }
    }
    return false;
}

function getStory($episodeID) {
    try {
        $dbConn = getConnection();
        $sql = "SELECT * FROM storyTable WHERE episodeID = :episodeID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':episodeID' => $episodeID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Error fetching story: " . $e->getMessage(), 0, $e);
    }
}

?>