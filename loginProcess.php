<?php
    require_once("functions.php");
    session_start();
    echo makePageStart("Login", "stylesheet.css");

    echo makeNavMenu("CyberPath");
    try{
    list($input, $errors) = validate_login(); //Creates an array with the given variables and uses the function validate_login() to check if user inputted details are correct.
    if ($errors) { //If there are any errors, do this.
         echo show_errors($errors); //Show all errors using show_errors.
         echo "<a class ='loginLink' href='loginForm.php'>Back to Login</a>\n"; //Link back to login form
    } else { //If user details are correct, do this.
        set_session('logged-in', true); //Set logged-in to true for session.
        header('Location: index.php'); //Send user back
    }     
    }
    catch (Exception $e) {
        echo "Problem " . $e->getMessage();
    }
        
    echo makeFooter();
    echo makePageEnd();
    ?>
