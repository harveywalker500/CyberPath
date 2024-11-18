<?php
/**
 * Filename: fetch_analytics.php
 * Author: Md Rifat
 * Description: This file contains the implementation of fetching and processing analytics data from the server.
 */


require 'functions.php'; // Database connection and helper functions

header('Content-Type: application/json');

// Get filters
$organizationID = $_GET['organizationID'] ?? null;

try {
    $pdo = getConnection();

    // Fetch organisation-level progress
    $orgQuery = "
        SELECT o.name AS organizationName, 
               COUNT(u.userID) AS totalUsers,
               AVG(up.quizCompleted) AS avgQuizCompleted,
               AVG(up.storyCompleted) AS avgStoryCompleted
        FROM organisationTable o
        LEFT JOIN userTable u ON o.organisationID = u.organisationID
        LEFT JOIN userProgressTable up ON u.userID = up.userID
        GROUP BY o.name";
    $orgResults = $pdo->query($orgQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user progress within the selected organization
    $userQuery = "
        SELECT u.username, 
               up.quizCompleted, 
               up.storyCompleted
        FROM userTable u
        LEFT JOIN userProgressTable up ON u.userID = up.userID
        WHERE (:organizationID IS NULL OR u.organisationID = :organizationID)";
    $stmt = $pdo->prepare($userQuery);
    $stmt->execute([':organizationID' => $organizationID]);
    $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'organizationProgress' => $orgResults,
        'userProgress' => $userResults,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
