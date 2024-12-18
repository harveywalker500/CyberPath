<?php
/**
 * Filename: fetch_analytics.php
 * Author: Md Rifat
 * Description: This file contains the implementation of fetching and processing analytics data from the server.
 */

session_start();
require 'functions.php'; // Database connection and helper functions

header('Content-Type: application/json');

// Get filters
$organisationID = isset($_SESSION['userID']) ? getUserOrganisation($_SESSION['userID']) : null;
$pdo = getConnection();

// Helper function for preparing and executing queries
function executeQuery($pdo, $query, $params = []) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

// Get request type
$type = $_GET['type'] ?? '';

/** ---------------------------------
 *  Overview Metrics
 * ---------------------------------*/
if ($type === 'overview') {
    $query = "
    SELECT 
        -- Total users in the organization
        (SELECT COUNT(*) 
         FROM userTable 
         WHERE organisationID = :orgID) AS totalUsers,

        -- Active users (employeeStatus table)
        (SELECT COUNT(*) 
         FROM employeeStatus 
         WHERE isActive = 1 
           AND userID IN (SELECT userID FROM userTable WHERE organisationID = :orgID)) AS activeUsers,

        -- Completed stories
        (SELECT COUNT(*) 
         FROM storyCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = :orgID)) AS completedStories,

        -- Completed episodes
        (SELECT COUNT(*) 
         FROM episodeCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = :orgID)) AS completedEpisodes,

        -- Average story completion time
        (SELECT AVG(durationInSeconds) 
         FROM storyCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = :orgID)) AS avgStoryTime,

        -- Average episode completion time
        (SELECT AVG(durationInSeconds) 
         FROM episodeCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = :orgID)) AS avgEpisodeTime
    ";

    $stmt = executeQuery($pdo, $query, [':orgID' => $organisationID]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

/** ---------------------------------
 *  Organization Comparison
 * ---------------------------------*/
if ($type === 'organization-comparison') {
    $query = "
    SELECT 
        o.name AS organization,
        (SELECT COUNT(*) FROM userTable WHERE organisationID = o.organisationID) AS totalUsers,
        (SELECT COUNT(*) 
         FROM employeeStatus 
         WHERE isActive = 1 
         AND userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS activeUsers,
        (SELECT COUNT(*) 
         FROM storyCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS completedStories,
        (SELECT COUNT(*) 
         FROM episodeCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS completedEpisodes,
        (SELECT AVG(durationInSeconds) 
         FROM storyCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS avgStoryTime,
        (SELECT AVG(durationInSeconds) 
         FROM episodeCompletionLog 
         WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS avgEpisodeTime
    FROM organisationTable o
    ";

    $stmt = $pdo->query($query);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

/** ---------------------------------
 *  User Progress
 * ---------------------------------*/
if ($type === 'user-progress') {
    $timeRange = $_GET['timeRange'] ?? 'month';

    // Dynamic date condition
    $dateCondition = match ($timeRange) {
        'week' => "DATE_SUB(NOW(), INTERVAL 1 WEEK)",
        'month' => "DATE_SUB(NOW(), INTERVAL 1 MONTH)",
        default => "1=1" // All time
    };

    $query = "
        SELECT 
            u.forename AS userName,
            DATE(ec.startTime) AS progressDate,
            COUNT(DISTINCT ec.episodeID) AS episodesCompleted,
            COUNT(DISTINCT sc.storyID) AS storiesCompleted
        FROM userTable u
        LEFT JOIN episodeCompletionLog ec 
            ON u.userID = ec.userID AND ec.startTime >= $dateCondition
        LEFT JOIN storyCompletionLog sc 
            ON u.userID = sc.userID AND sc.startTime >= $dateCondition
        WHERE u.organisationID = :organisationID
        GROUP BY u.userName, DATE(ec.startTime)
        ORDER BY progressDate ASC
    ";

    $stmt = executeQuery($pdo, $query, [':organisationID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

/** ---------------------------------
 *  Reusable Metric Fetching
 * ---------------------------------*/
$metricTypes = [
    'total-users' => "SELECT forename AS name FROM userTable WHERE organisationID = :orgID",
    'active-users' => "
        SELECT u.forename AS name
        FROM userTable u
        JOIN employeeStatus es ON u.userID = es.userID
        WHERE es.isActive = 1 AND u.organisationID = :orgID
    ",
    'completed-stories' => "
        SELECT u.forename AS name, SUM(scl.durationInSeconds) AS storyTime
        FROM storyCompletionLog scl
        JOIN userTable u ON scl.userID = u.userID
        WHERE u.organisationID = :orgID
        GROUP BY u.userID
    ",
    'completed-episodes' => "
        SELECT u.forename AS name, SUM(ecl.durationInSeconds) AS episodeTime
        FROM episodeCompletionLog ecl
        JOIN userTable u ON ecl.userID = u.userID
        WHERE u.organisationID = :orgID
        GROUP BY u.userID
    ",
    'avg-story-time' => "
        SELECT AVG(durationInSeconds) AS avgTime 
        FROM storyCompletionLog scl
        JOIN userTable u ON scl.userID = u.userID
        WHERE u.organisationID = :orgID
    ",
    'avg-episode-time' => "
        SELECT AVG(durationInSeconds) AS avgTime 
        FROM episodeCompletionLog ecl
        JOIN userTable u ON ecl.userID = u.userID
        WHERE u.organisationID = :orgID
    "
];

if (isset($metricTypes[$type])) {
    $stmt = executeQuery($pdo, $metricTypes[$type], ['orgID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

echo json_encode(['error' => 'Invalid request type']);
exit;
