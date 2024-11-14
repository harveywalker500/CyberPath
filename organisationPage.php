<?php
// Include functions file and start session
require_once("functions.php");
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: loginForm.php");
    exit();
}

$errors = []; // Initialize errors array

// Fetch all organisations from the database
try {
    $dbConn = getConnection();
    $sql = "SELECT organisationID, name FROM organisationTable";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errors[] = "Error fetching organisations: " . $e->getMessage();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['createOrganisation'])) {
        $organisationName = trim($_POST['organisationName'] ?? '');
        
        if (empty($organisationName)) {
            $errors[] = "Please provide an organisation name.";
        }

        if (empty($errors)) {
            try {
                // Insert the new organisation into the database
                $sql = "INSERT INTO organisationTable (name) VALUES (:name)";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':name' => $organisationName]);

                // Get the newly created organisation ID
                $organisationID = $dbConn->lastInsertId();

                // Assign the user to the newly created organisation
                $userID = $_SESSION['userID'];
                $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);

                // Reload the organisations list after creation
                $sql = "SELECT organisationID, name FROM organisationTable";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute();
                $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Re-fetch organisations

                echo "<div class='notification is-success'>Organisation created successfully!</div>";
            } catch (Exception $e) {
                $errors[] = "Error creating organisation: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['joinOrganisation'])) {
        $organisationID = $_POST['organisationID'] ?? null;

        if (empty($organisationID)) {
            $errors[] = "Please select an organisation.";
        }

        if (empty($errors)) {
            try {
                // Assign the user to the selected organisation
                $userID = $_SESSION['userID'];
                $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);

                echo "<div class='notification is-success'>You have successfully joined the organisation.</div>";
            } catch (Exception $e) {
                $errors[] = "Error joining organisation: " . $e->getMessage();
            }
        }
    }
}

echo makePageStart("Manage Organisation - CyberPath");
echo makeNavMenu("organisation.php"); // Pass the current page name

?>
