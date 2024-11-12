<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getConnection(){ //function to get the connection to the database, allows users to query
    try{
        $connection = new PDO("mysql:host=nuwebspace_db; dbname=w22009720","w22009720", "Bwjddnxa");//PDO is data abstraction layer
        $connection ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//sets attributes on PDO connection. Turns errors and exception reporting on.
        return $connection;
        
    }catch(Exception $e) {
        throw new Exception("Connection error".$e->getMessage(), 0 ,$e);
    }
}

function makePageStart($title) 
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="stylesheet.csss">
    </head>
    <body>
HTML;
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
            } else {
                $errors[] = "Password is incorrect.";
            }
        } else {
            $errors[] = "Username is incorrect.";
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
            header('Location: loginForm.php');//Redirects user
            exit();//Terminates script
        }
}

function logOut(){//Function to log out of account
    $_SESSION = array();//Reset session array
    session_destroy();//Destroys current session
    header('Location: index.php');//Redirects user to home page.
    exit();//Terminates script
}

function makeNavMenu($navMenuHeader, array $links) {
    $output = <<<HTML
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <img src="CyberPath.png" alt="Site Logo" style="width: 100px; height: 100px; max-height: none;">
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

    foreach ($links as $key => $value) {
        $output .= "<a class=\"navbar-item\" href=\"$key\">$value</a>\n";
    }

    if (check_login()) {
        // Fetch organization info if needed
        $userID = get_session('userID');
        $dbConn = getConnection();
        $sql = "SELECT organisationID FROM userTable WHERE userID = :userID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($organisation) {
            // Display organization name or manage link
            $output .= "<a class=\"navbar-item\" href='manageOrganisation.php?id=" . $organisation['organisationID'] . "'>Manage Organization</a>\n";
        }
        $output .= "<a class=\"navbar-item\" href='logout.php'>Logout</a>\n";
    } else {
        $output .= "<a class=\"navbar-item\" href='loginForm.php'>Login</a>\n";
        $output .= "<a class=\"navbar-item\" href='register.php'>Register</a>\n";
    }

    $output .= <<<HTML
            </div>
        </div>
    </nav>
HTML;

    return $output;
}




function makeFooter($footerText) {
    return <<<HTML
    <footer>
        <p>$footerText</p>
    </footer>
HTML;
}

function makePageEnd() {
    return <<<HTML
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const burger = document.querySelector('.navbar-burger');
                const menu = document.querySelector('.navbar-menu');
                
                burger.addEventListener('click', () => {
                    burger.classList.toggle('is-active');
                    menu.classList.toggle('is-active');
                });
            });
        </script>
    </body>
</html>
HTML;
}

function has_completed_part($partNumber) {
    // Example logic: Check if the user has completed part $partNumber (using a session variable or database check)
    // For now, we'll assume parts 1-3 are completed, and 4-6 are not.
    $completed_parts = [1, 2, 3]; // Replace with actual logic
    return in_array($partNumber, $completed_parts);
}

function getUserOrganization($userID) {
    try {
        $dbConn = getConnection();
        $sql = "SELECT organisationID FROM userTable WHERE userID = :userID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);
        return $organisation ? $organisation['organisationID'] : null;
    } catch (Exception $e) {
        throw new Exception("Error fetching user organization: " . $e->getMessage(), 0, $e);
    }
}

function getOrganization($organisationID) {
    try {
        $dbConn = getConnection();
        $sql = "SELECT * FROM organisationTable WHERE organisationID = :organisationID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':organisationID' => $organisationID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Error fetching organization: " . $e->getMessage(), 0, $e);
    }
}


?>