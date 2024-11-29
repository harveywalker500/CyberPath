function generateColors(count) {
    const colors = [];
    for (let i = 0; i < count; i++) {
        const hue = (i * (360 / count)) % 360; // Distribute hues evenly
        colors.push(`hsl(${hue}, 70%, 60%)`); // HSL format for distinct colors
    }
    return colors;
}

function fetchOverview() {
    fetch("../php/fetch_analytics.php?type=overview")
        .then(response => response.json())
        .then(data => {
            document.getElementById("totalUsers").innerText = data.totalUsers;
            document.getElementById("activeUsers").innerText = data.activeUsers;
            document.getElementById("completedStories").innerText = data.completedStories;
            document.getElementById("completedEpisodes").innerText = data.completedEpisodes;
            document.getElementById("avgStoryTime").innerText = `${Math.round(data.avgStoryTime / 60)} mins`;
            document.getElementById("avgEpisodeTime").innerText = `${Math.round(data.avgEpisodeTime / 60)} mins`;
        });
}


let currentUserChart;

// Fetch user progress data
function fetchUserProgress(timeRange = 'month') {
    fetch(`../php/fetch_analytics.php?type=user-progress&timeRange=${timeRange}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error:", data.error);
            } else {
                renderUserProgressChart(data, timeRange);
            }
        })
        .catch(error => console.error("Error fetching user progress data:", error));
}

// Render the user progress chart
function renderUserProgressChart(data, timeRange) {
    const ctx = document.getElementById("userProgressChart").getContext("2d");

    // Extract unique users and dates
    const users = [...new Set(data.map(item => item.userName))];
    const dates = [...new Set(data.map(item => item.date))].sort();

    // Prepare datasets for each user
    const datasets = users.map(user => {
        // Cumulative progress for each date
        const cumulativeData = dates.map(date => {
            const entry = data.find(d => d.userName === user && d.date === date);
            return entry ? entry.episodesCompleted + entry.storiesCompleted : 0;
        });

        // Generate a unique color for each user
        const colorHue = Math.random() * 360;
        const backgroundColor = `hsl(${colorHue}, 70%, 50%)`;
        const borderColor = `hsl(${colorHue}, 80%, 40%)`;

        return {
            label: user,
            data: cumulativeData,
            backgroundColor: backgroundColor,
            borderColor: borderColor,
            borderWidth: 2,
            fill: false
        };
    });

    // Destroy previous chart instance if it exists
    if (currentUserChart) {
        currentUserChart.destroy();
    }

    currentUserChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: dates, // Time progression (weekly or monthly)
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: `User Progress (${timeRange.charAt(0).toUpperCase() + timeRange.slice(1)})`
                },
                legend: {
                    display: true,
                    position: "top"
                },
                tooltip: {
                    callbacks: {
                        label: (tooltipItem) => `${tooltipItem.dataset.label}: ${tooltipItem.raw}`
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Date"
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Cumulative Progress (Episodes + Stories)"
                    }
                }
            }
        }
    });
}

// Event listener for time range selection
document.getElementById("time-range-select").addEventListener("change", (event) => {
    const selectedRange = event.target.value;
    fetchUserProgress(selectedRange);
});


let currentChart;

// Fetch data and render the chart
function fetchOrganizationComparison() {
    fetch("../php/fetch_analytics.php?type=organization-comparison")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error:", data.error);
            } else {
                // Render chart for the first metric by default ("Total Users")
                renderComparisonChart(data, "totalUsers");

                // Add event listener to the dropdown for metric selection
                document.getElementById("metric-select").addEventListener("change", (event) => {
                    const selectedMetric = event.target.value;
                    renderComparisonChart(data, selectedMetric);
                });
            }
        })
        .catch(error => console.error("Error fetching organization comparison data:", error));
}

// Render the chart for a specific metric
function renderComparisonChart(data, metric) {
    const ctx = document.getElementById("organizationComparisonChart").getContext("2d");

    const labels = data.map(org => org.organization); // Organization names as labels
    const values = data.map(org => org[metric]); // Metric values for each organization
    const chartLabel = metric.replace(/([A-Z])/g, ' $1').trim(); // Format the metric name for the label
    const colors = generateColors(data.length); // Generate unique colors for each organization

    // Destroy previous chart instance if it exists
    if (currentChart) {
        currentChart.destroy();
    }

    currentChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: chartLabel,
                data: values,
                backgroundColor: colors, // Apply unique colors
                borderColor: colors.map(color => color.replace("60%", "50%")), // Slightly darker border
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: chartLabel
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: "Organizations"
                    }
                }
            },
            plugins: {
                legend: {
                    display: false // Only one dataset, no need for legend
                },
                tooltip: {
                    callbacks: {
                        label: (tooltipItem) => `${tooltipItem.dataset.label}: ${tooltipItem.raw}`
                    }
                }
            }
        }
    });
}


document.addEventListener("DOMContentLoaded", () => {
    fetchOverview();
    fetchOrganizationComparison();
    fetchUserProgress();

});
