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

// Get request type
$type = $_GET['type'] ?? '';


$pdo= getConnection();


// Get request type
$type = $_GET['type'] ?? '';

if ($type === 'overview') {

    $query = "
    SELECT 
    -- Total users in the organization
    (SELECT COUNT(*) 
     FROM userTable 
     WHERE organisationID = :orgID) AS totalUsers,

    -- Active users (based on employeeStatus table)
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



    $stmt = $pdo->prepare($query);
    $stmt->execute([':orgID' => $organisationID]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}




if ($type === 'organization-comparison') {
    $query = "
    SELECT 
        o.name AS organization,
        (SELECT COUNT(*) FROM userTable WHERE organisationID = o.organisationID) AS totalUsers,
        (SELECT COUNT(*) 
            FROM employeeStatus 
            WHERE isActive = 1 
            AND userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS activeUsers,
        (SELECT COUNT(*) FROM storyCompletionLog 
            WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS completedStories,
        (SELECT COUNT(*) FROM episodeCompletionLog 
            WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS completedEpisodes,
        (SELECT AVG(durationInSeconds) FROM storyCompletionLog 
            WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS avgStoryTime,
        (SELECT AVG(durationInSeconds) FROM episodeCompletionLog 
            WHERE userID IN (SELECT userID FROM userTable WHERE organisationID = o.organisationID)) AS avgEpisodeTime
    FROM organisationTable o
";


    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
    exit;
}








if ($type === 'user-progress') {
    $timeRange = $_GET['timeRange'] ?? 'month'; // Default to 'month'

    $dateCondition = ($timeRange === 'week') 
        ? "DATE_SUB(NOW(), INTERVAL 1 WEEK)" 
        : "DATE_SUB(NOW(), INTERVAL 1 MONTH)";

    $query = "
        SELECT 
            u.forename AS userName,
            DATE(ec.startTime) AS progressDate,
            COUNT(DISTINCT ec.episodeID) AS episodesCompleted,
            COUNT(DISTINCT sc.storyID) AS storiesCompleted
        FROM userTable u
        LEFT JOIN episodeCompletionLog ec 
            ON u.userID = ec.userID AND ec.startTime >= :dateCondition
        LEFT JOIN storyCompletionLog sc 
            ON u.userID = sc.userID AND sc.startTime >= :dateCondition
        WHERE u.organisationID = :organisationID
        GROUP BY u.userName, DATE(ec.startTime)
        ORDER BY progressDate ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':dateCondition' => $dateCondition,
        ':organisationID' => $organisationID
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
    exit;
}


if ($type === 'total-users') {
    $query = "SELECT forename AS name FROM userTable WHERE organisationID = :orgID";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 

if ($type === 'active-users') {
    $query = "
            SELECT u.forename AS name
            FROM userTable u
            JOIN employeeStatus es ON u.userID = es.userID
            WHERE es.isActive = 1 AND u.organisationID = :orgID
        ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 


if ($type === 'completed-stories') {
    $query = "SELECT u.forename AS name, SUM(scl.durationInSeconds) AS storyTime
              FROM storyCompletionLog scl
              JOIN userTable u ON scl.userID = u.userID
              WHERE u.organisationID = :orgID
              GROUP BY u.userID";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 

if ($type === 'completed-episodes') {
    $query = "SELECT u.forename AS name, SUM(ecl.durationInSeconds) AS episodeTime
              FROM episodeCompletionLog ecl
              JOIN userTable u ON ecl.userID = u.userID
              WHERE u.organisationID = :orgID
              GROUP BY u.userID";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($type === 'avg-story-time') {
    $query = "SELECT AVG(durationInSeconds) AS avgTime FROM storyCompletionLog scl
              JOIN userTable u ON scl.userID = u.userID
              WHERE u.organisationID = :orgID";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} 

if ($type === 'avg-episode-time') {
    $query = "SELECT AVG(durationInSeconds) AS avgTime FROM episodeCompletionLog ecl
              JOIN userTable u ON ecl.userID = u.userID
              WHERE u.organisationID = :orgID";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['orgID' => $organisationID]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
