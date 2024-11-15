<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session.

// Start the page and include the navigation menu
echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");
?>

<div class="container">
    <section class="hero is-info is-bold">
        <div class="hero-body">
            <div class="container">
                <h1 class="title is-1">Welcome to CyberPath!</h1>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="content">
            <?php if (loggedIn()): ?>
                <!-- Show this if the user is logged in -->
                <p class="has-text-weight-semibold">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! Welcome back to CyberPath!</p>
            <?php else: ?>
                <!-- Show this if the user is not logged in -->
                <p>Welcome to CyberPath, your destination for interactive learning and challenges in cybersecurity. Whether you're a beginner or a seasoned pro, we have a range of stories and quizzes designed to test your knowledge and improve your skills.</p>

                <p>If you're new here, you can <a href="register.php">sign up</a> to start your journey. If you're returning, please <a href="loginForm.php">log in</a> to continue where you left off.</p>
            <?php endif; ?>

            <p>Navigate through various episodes, make decisions that influence the outcome of the story, and answer quizzes to assess your knowledge. Ready to take the challenge? Let's get started!</p>
        </div>
    </section>
</div>

<script src="index.js"></script>

<?php
// Add the footer
echo makeFooter();
echo makePageEnd();
?>
