<?php
require_once("functions.php");

// Start the page
echo makePageStart("Bulma Test Page", "style.css");

// Test Bulma with a sample button
echo <<<HTML
<section class="section">
    <div class="container">
        <h1 class="title">Bulma Test</h1>
        <p class="subtitle">If Bulma is working, the button below should be styled:</p>
        <button class="button is-primary">Test Button</button>
    </div>
</section>
HTML;

// Footer and end
echo makeFooter("Footer content here");
echo makePageEnd();
?>