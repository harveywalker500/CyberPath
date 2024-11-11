<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="$stylesheet">
    </head>
    <body>
HTML;
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


function makeNavMenu($navMenuHeader, array $links) {
    // Initial navbar HTML structure with Bulma classes
    $output = <<<HTML
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <!-- Logo -->
            <a class="navbar-item" href="index.php">
                <img src="..." alt="Site Logo" width="112" height="28">
            </a>

            <!-- Burger menu for mobile view -->
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasic">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasic" class="navbar-menu">
            <div class="navbar-start">
HTML;

    // Loop through the provided links array and add them as navbar items
    foreach ($links as $key => $value) {
        $output .= "<a class=\"navbar-item\" href=\"$key\">$value</a>\n";
    }

    // Conditional links based on login status
    if (check_login() === true) {
        $output .= "<a class=\"navbar-item\" href='logout.php'>Logout</a>\n";
    } else {
        $output .= "<a class=\"navbar-item\" href='loginForm.php'>Login</a>\n";
        $output .= "<a class=\"navbar-item\" href='register.php'>Register</a>\n";
    }

    // Close the navbar divs
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

?>