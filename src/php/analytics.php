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

$organisationID = isset($_SESSION['userID']) ? getUserOrganisation($_SESSION['userID']) : null;

$organisationName = "";
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT name FROM organisationTable WHERE organisationID = :organisationID");
    $stmt->bindParam(':organisationID', $organisationID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $organisationName = $result['name'] ?? "Unknown";
    }
} catch (Exception $e) {
    $organisationName = "Unknown";
    error_log("Error fetching organization name: " . $e->getMessage());
}


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
                <p>Organisation: <strong><?php echo htmlspecialchars($organisationName); ?></strong></p>
                <p>Track and analyze your team’s performance.</p>
                <button id="exportasPDF" class="button">Export as PDF</button>
            </header>

            <!-- Overview Section -->
            <section id="overview">
                <h2>Organisation Analytics</h2>
                <div id="organizationAnalytics" class="analytics-overview">
                    <div class="overview-item" data-metric="totalUsers">
                        <span class="label">Total Users</span>
                        <span class="value" id="totalUsers">5</span>
                    </div>
                    <div class="overview-item" data-metric="activeUsers">
                        <span class="label">Active Users</span>
                        <span class="value" id="activeUsers">3</span>
                    </div>
                    <div class="overview-item" data-metric="completedStories">
                        <span class="label">Story Completions</span>
                        <span class="value" id="completedStories">30</span>
                    </div>
                    <div class="overview-item" data-metric="completedEpisodes">
                        <span class="label">Episode Completions</span>
                        <span class="value" id="completedEpisodes">30</span>
                    </div>
                    <div class="overview-item" data-metric="avgStoryTime">
                        <span class="label">Avg Story Time</span>
                        <span class="value" id="avgStoryTime">35 mins</span>
                    </div>
                    <div class="overview-item" data-metric="avgEpisodeTime">
                        <span class="label">Avg Episode Time</span>
                        <span class="value" id="avgEpisodeTime">23 mins</span>
                    </div>
                </div>
                <div id="tooltip" class="tooltip hidden"></div>
            </section>

            <!-- User Progress Canvas section -->
            <section id="user-progress" class="userprogress-container">
                <h2>User Progress Over Time</h2>
                <div class="controls">
                    <label for="time-range-select">Time Range:</label>
                    <select id="time-range-select">
                        <option value="week">Last Week</option>
                        <option value="month">Last Month</option>
                        <option value="all" selected>All Time</option>
                    </select>
                </div>
                <div id="userProgressWrapper" style="max-height: 400px; overflow-y: auto;">
                <canvas id="userProgressChart"></canvas>
            </div>

            </section>


            <!-- Organization Comparison Canvas Section-->
            <section id="organization-comparison">
                <h2>Organisational Comparison</h2>
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
                <div id="organizationComparisonWrapper" style="max-height: 400px; overflow-y: auto;">
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
    document.getElementById('exportasPDF').addEventListener('click', function () {
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

