<?php
require_once("functions.php");
session_start();
echo makePageStart("Login");
echo makeNavMenu("CyberPath");
?>

<div class="container mt-5">
    <div class="columns is-centered">
        <div class="column is-half">
            <form id="loginForm" action="loginProcess.php" method="post" class="box">
                <fieldset>
                    <legend class="title is-4 has-text-centered">Login</legend>

                    <!-- Username field -->
                    <div class="field">
                        <label for="username" class="label">Username</label>
                        <div class="control">
                            <input type="text" name="username" id="username" class="input" placeholder="Enter your username">
                        </div>
                    </div>

                    <!-- Password field -->
                    <div class="field">
                        <label for="password" class="label">Password</label>
                        <div class="control">
                            <input type="password" name="password" id="password" class="input" placeholder="Enter your password">
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">Log in</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>


<?php
echo makeFooter("Footer");
echo makePageEnd();
?>