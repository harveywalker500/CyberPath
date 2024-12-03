<?php
/**
 * File: analytics.php
 * author: Md Rifat
 * Description: This file generates the analytics page for the CyberPath project, including organization-level and user-level progress visualizations, and provides functionality to export the page as a PDF.
*/



require 'functions.php';

session_start(); // Starts the session
loggedIn(); // Ensures the user is logged in before loading the page

echo makePageStart("CyberPath | Analytics", "../../css/stylesheet.css");
echo makeNavMenu("CyberPath");

?>

<div class="dashboard">
        <!-- Sidebar -->
        <!-- <div class="sidebar">
            <h2 class="sidebar-title">CyberPath</h2>
            <ul class="sidebar-menu">
                <li><a href="#overview" class="sidebar-link">Overview</a></li>
                <li><a href="#user-stats" class="sidebar-link">User Statistics</a></li>
                <li><a href="#trends" class="sidebar-link">Activity Trends</a></li>
            </ul>
        </div> -->

        <!-- Main Content -->
        <div class="content">
            <header class="content-header">
                <h1>Team Leader Dashboard</h1>
                <p>Track and analyze your team’s performance.</p>
                <button id="exportPDF" class="button">Export PDF</button>
            </header>

            <!-- Overview Section -->
            <section id="overview">
    <h2>Organization Analytics</h2>
    <div class="overview-container">
        <div class="metric">
            <h3>Total Users</h3>
            <p id="totalUsers">0</p>
        </div>
        <div class="metric">
            <h3>Active Users</h3>
            <p id="activeUsers">0</p>
        </div>
        <div class="metric">
            <h3>Completed Stories</h3>
            <p id="completedStories">0</p>
        </div>
        <div class="metric">
            <h3>Completed Episodes</h3>
            <p id="completedEpisodes">0</p>
        </div>
        <div class="metric">
            <h3>Avg Story Time</h3>
            <p id="avgStoryTime">0 mins</p>
        </div>
        <div class="metric">
            <h3>Avg Episode Time</h3>
            <p id="avgEpisodeTime">0 mins</p>
        </div>
    </div>
</section>

<section id="user-progress" class="userprogress-container">
    <h2>User Progress Over Time</h2>
    <div class="controls">
        <label for="time-range-select">Time Range:</label>
        <select id="time-range-select">
            <option value="week">Last Week</option>
            <option value="month" selected>Last Month</option>
        </select>
    </div>
    <div class="chart-container">
        <canvas id="userProgressChart"></canvas>
    </div>
</section>



<section id="organization-comparison">
    <h2>Organizational Comparison</h2>
    <div class="controls">
        <label for="metric-select">Select Metric:</label>
        <select id="metric-select">
            <option value="totalUsers">Total Users</option>
            <option value="activeUsers">Active Users</option>
            <option value="completedStories">Completed Stories</option>
            <option value="completedEpisodes">Completed Episodes</option>
            <option value="avgStoryTime">Average Story Time</option>
            <option value="avgEpisodeTime">Average Episode Time</option>
        </select>
    </div>
    <div class="chart-container">
        <canvas id="organizationComparisonChart"></canvas>
    </div>
</section>




        </div>
    </div>


    


<?php
echo makeFooter();

?>
<!-- export module -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- chart modle -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<!-- export js -->
<script>
    document.getElementById('exportPDF').addEventListener('click', function () {
    const pdf = new jspdf.jsPDF('p', 'mm', 'a4'); // Create a PDF document
    const content = document.body; // Select the full page content to be exported

    // Temporarily remove the body's scrollbars to ensure the full content is rendered
    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    // Adjust html2canvas to capture the full page
    html2canvas(content, {
        scrollY: 0, // Ensure it starts from the top
        useCORS: true, // Handle cross-origin images
        windowWidth: document.body.scrollWidth, // Full page width
        windowHeight: document.body.scrollHeight // Full page height
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/png'); // Convert canvas to image
        const imgWidth = 190; // A4 page width minus margins
        const pageHeight = 290; // A4 page height minus margins
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Scale image height
        let position = 0; // Current vertical position in the PDF

        // Add content to the PDF, handling multi-page if needed
        while (position < imgHeight) {
            pdf.addImage(imgData, 'PNG', 10, position - pageHeight * Math.floor(position / pageHeight), imgWidth, imgHeight);
            position += pageHeight;
            if (position < imgHeight) {
                pdf.addPage();
            }
        }

        // Restore the body's original overflow property
        document.body.style.overflow = originalOverflow;

        pdf.save('analytics.pdf'); // Save and download the PDF
    }).catch(error => {
        console.error('Error generating PDF:', error);

        // Restore the body's original overflow property in case of error
        document.body.style.overflow = originalOverflow;
    });
});


</script>

<script src="../js/analytics.js"></script>
<?php
echo makePageEnd();
?>

