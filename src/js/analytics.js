// Generate persistent colors for users or organizations
const generateColors = (() => {
    const colorCache = {};
    return (keys) => {
        return keys.map((key, index) => {
            if (!colorCache[key]) {
                const hue = (index * (360 / keys.length)) % 360;
                colorCache[key] = `hsl(${hue}, 70%, 60%)`;
            }
            return colorCache[key];
        });
    };
})();

// Fetch overview metrics
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

// Persistent chart instance
let currentUserChart;

// Fetch user progress data
function fetchUserProgress(timeRange = "month") {
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

// Render user progress chart
function renderUserProgressChart(data, timeRange) {
    const ctx = document.getElementById("userProgressChart").getContext("2d");

    // Extract unique users and progress dates
    const users = [...new Set(data.map(item => item.userName))];
    const dates = [...new Set(data.map(item => item.progressDate))].sort(); // Use progressDate instead of date
    const colors = generateColors(users); // Generate persistent colors for users

    // Prepare datasets for each user
    const datasets = users.map((user, index) => {
        let cumulativeEpisodes = 0;
        let cumulativeStories = 0;

        const userProgress = dates.map(date => {
            const entry = data.find(d => d.userName === user && d.progressDate === date);

            if (entry) {
                cumulativeEpisodes += entry.episodesCompleted || 0;
                cumulativeStories += entry.storiesCompleted || 0;
            }

            // Return cumulative progress (episodes + stories)
            return cumulativeEpisodes + cumulativeStories;
        });

        return {
            label: user,
            data: userProgress,
            backgroundColor: colors[index],
            borderColor: colors[index].replace("60%", "50%"),
            borderWidth: 2,
            tension: 0.4, // Smoother line
            fill: false,
        };
    });

    // Adjust the wrapper for scrolling if necessary
    const wrapper = document.getElementById("userProgressWrapper");
    if (users.length > 15 || dates.length > 15) {
        wrapper.style.maxHeight = "500px"; // Adjust height for large data
        wrapper.style.overflowX = "auto"; // Enable horizontal scrolling
        wrapper.style.overflowY = "hidden"; // Prevent vertical scrolling
    } else {
        wrapper.style.maxHeight = ""; // Reset if data is small
        wrapper.style.overflowX = "";
        wrapper.style.overflowY = "";
    }

    // Destroy previous chart instance if it exists
    if (currentUserChart) {
        currentUserChart.destroy();
    }

    // Create the chart
    currentUserChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: dates, // Use sorted dates as X-axis labels
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow resizing
            plugins: {
                title: {
                    display: true,
                    text: `User Progress (${timeRange.charAt(0).toUpperCase() + timeRange.slice(1)})`,
                },
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    callbacks: {
                        label: (tooltipItem) => {
                            return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Date",
                    },
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Cumulative Progress (Episodes + Stories)",
                    },
                },
            },
        },
    });
}



// Persistent chart instance
let currentChart;

// Fetch organization comparison data
function fetchOrganizationComparison() {
    fetch("../php/fetch_analytics.php?type=organization-comparison")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error:", data.error);
            } else {
                renderComparisonChart(data, "totalUsers");

                document.getElementById("metric-select").addEventListener("change", (event) => {
                    const selectedMetric = event.target.value;
                    renderComparisonChart(data, selectedMetric);
                });
            }
        })
        .catch(error => console.error("Error fetching organization comparison data:", error));
}

// Render organization comparison chart
function renderComparisonChart(data, metric) {
    const ctx = document.getElementById("organizationComparisonChart").getContext("2d");

    const labels = data.map(org => org.organization); // Organization names as labels
    const values = data.map(org => org[metric]); // Metric values for each organization
    const chartLabel = metric.replace(/([A-Z])/g, ' $1').trim(); // Format the metric name for the label
    const colors = generateColors(labels); // Generate unique colors for each organization

    // Adjust the height of the chart wrapper based on the number of organizations
    const wrapper = document.getElementById("organizationComparisonWrapper");
    const organizationCount = labels.length;
    if (organizationCount > 15) {
        wrapper.style.maxHeight = "500px"; // Set a maximum height
        wrapper.style.overflowY = "auto"; // Enable vertical scrolling
    } else {
        wrapper.style.maxHeight = ""; // Reset the height if less than 15 organizations
        wrapper.style.overflowY = ""; // Remove scrolling
    }

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
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace("60%", "50%")),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                    display: false
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



// Event Listeners and Initialization
document.addEventListener("DOMContentLoaded", () => {
    fetchOverview();
    fetchOrganizationComparison();
    fetchUserProgress();

    document.getElementById("time-range-select").addEventListener("change", (event) => {
        fetchUserProgress(event.target.value);
    });
});
