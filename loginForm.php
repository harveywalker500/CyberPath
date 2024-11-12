<?php
require_once("functions.php");
session_start();
echo makePageStart("Login");
echo makeNavMenu("CyberPath", array("index.php" => "Home", "story.php" => "Story", "quizSelection.php" => "Quiz Selection", "leaderboard.php"  => "Leaderboard"));
?>

<form id="loginForm" action="loginProcess.php" method="post">
    <fieldset>
        <legend>Login</legend>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" >

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" >

        <input type="submit" value="Log in" >
    </fieldset>
</form> <!-- Form for login function -->

<?php
echo makeFooter("Footer");
echo makePageEnd();
?>