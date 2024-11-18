<?php
/**
 * File: analytics.php
 * author: Md Rifat
 * Description: This file generates the analytics page for the CyberPath project, including organization-level and user-level progress visualizations, and provides functionality to export the page as a PDF.
*/



require 'functions.php';


echo makePageStart("CyberPath | Analytics");
echo makeNavMenu("CyberPath");

?>

    <header class="header">
        <h1>Organization Analytics</h1>
        <button id="exportPDF" class="button">Export PDF</button>
    </header>
    <main class="main">
        <!-- Organization-Level Progress -->
        <section class="section">
            <h2 class="h2">Organization Comparison</h2>
            <canvas id="orgChart"></canvas>
        </section>

        <!-- User-Level Progress -->
        <section>
            <h2 class="h2">User Progress</h2>
            <table class="table">
                <thead class="thread">
                    <tr class="tr">
                        <th class="th">Username</th>
                        <th class="th">Quizzes Completed</th>
                        <th class="th">Stories Completed</th>
                    </tr>
                </thead>
                <tbody id="userProgressTable">
                    <!-- Populate dynamically -->
                </tbody>
            </table>
        </section>
    </main>

    <link rel="stylesheet" href="analytics.css"> <!-- Include analytics.css here -->
    


<?php
echo makeFooter();

?>
<!-- export module -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- chart modle -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<!-- export js -->
<script>
    document.getElementById('exportPDF').addEventListener('click', function () {
        const pdf = new jspdf.jsPDF('p', 'mm', 'a4'); // Create a PDF document
        const content = document.body; // Capture the content to be exported (adjust as needed)

        html2canvas(content).then(canvas => {
            const imgData = canvas.toDataURL('image/png'); // Convert canvas to image
            const imgWidth = 190; // A4 page width minus margins
            const pageHeight = 290; // A4 page height minus margins
            const imgHeight = (canvas.height * imgWidth) / canvas.width; // Scale image height
            let position = 0; // Current vertical position in the PDF

            // If content fits on one page
            if (imgHeight <= pageHeight) {
                pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
            } else {
                // Multi-page logic
                while (position < imgHeight) {
                    pdf.addImage(imgData, 'PNG', 10, position - pageHeight, imgWidth, imgHeight);
                    position += pageHeight;
                    if (position < imgHeight) {
                        pdf.addPage();
                    }
                }
            }

            pdf.save('analytics.pdf'); // Save and download the PDF
        }).catch(error => {
            console.error('Error generating PDF:', error);
        });
    });

</script>

<script>src="burger.js"</script>
<script src="analytics.js"></script>
</body>
</html>

