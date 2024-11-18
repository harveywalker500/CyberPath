/**
 * @file analytics.js
 * @author Md Rifat
 * @description This script fetches analytics data from the server and updates the organization comparison chart and user progress table on the webpage.
 */


document.addEventListener('DOMContentLoaded', function () {
    fetch('fetch_analytics.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Organization Comparison Chart
            const orgLabels = data.organizationProgress.map(org => org.organizationName);
            const orgUsers = data.organizationProgress.map(org => org.totalUsers);
            const orgCtx = document.getElementById('orgChart').getContext('2d');
            new Chart(orgCtx, {
                type: 'bar',
                data: {
                    labels: orgLabels,
                    datasets: [{
                        label: 'Total Users',
                        data: orgUsers,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                }
            });

            // User Progress Table
            const userProgressTable = document.getElementById('userProgressTable');
            userProgressTable.innerHTML = '';
            data.userProgress.forEach(user => {
                const row = `<tr>
                    <td>${user.username}</td>
                    <td>${user.quizCompleted || 0}</td>
                    <td>${user.storyCompleted || 0}</td>
                </tr>`;
                userProgressTable.innerHTML += row;
            });
        })
        .catch(error => console.error('Error:', error));
});
