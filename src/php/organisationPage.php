<!--  Organisation Page to create or join an organisation  -->

<?php
// Include functions file and start session
require_once("functions.php");
session_start();
loggedIn(); // Ensures the user is logged in before loading the page


// Redirect to login page if the user is not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: loginForm.php");
    exit();
}


$userID = $_SESSION['userID']; // Get UserID from database when logged in
$successMessage = ""; // Initalise success message
$errors = []; // Initialise error array

// Connect to database and fetch all organisations from the database
try {
    $dbConn = getConnection(); // Establish database connection
    $sql = "SELECT organisationID, name FROM organisationTable";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all organisations 
    $sql = "SELECT organisationID, teamLeaderID FROM organisationTable WHERE teamLeaderID = :userID";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute([':userID' => $userID]);
    $teamLeaderOrg = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user is already part of any other organisation
    $sql = "SELECT organisationID FROM userTable WHERE userID = :userID";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute([':userID' => $userID]);
    $currentOrgID = $stmt->fetchColumn();
    $isTeamLeader = false;

    // Fetch current organisation name
    if ($currentOrgID) {
        $sql = "SELECT name FROM organisationTable WHERE organisationID = :organisationID";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':organisationID' => $currentOrgID]);
        $currentOrgName = $stmt->fetchColumn();
    } else {
        $currentOrgName = "You are not part of any organisation. Create or join an organisation.";
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
                    $sql = "INSERT INTO organisationTable (name, teamLeaderID) VALUES (:name, :teamLeaderID)";
                    $stmt = $dbConn->prepare($sql);
                    // Include the current user as the team leader
                    $stmt->execute([':name' => $organisationName, ':teamLeaderID' => $userID]);

                    // Get the newly created organisation ID
                    $organisationID = $dbConn->lastInsertId();

                    // Assign the user to the newly created organisation
                    $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                    $stmt = $dbConn->prepare($sql);
                    $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);
                    $_SESSION['successMessage'] = "Organisation created and you have been assigned as the team leader.";

                    // Refreshes the page and data from database
                    header("Location: organisationPage.php");
                    exit();
                } catch (Exception $e) {
                    $errors[] = "Error creating organisation: " . $e->getMessage();
                }
            }
            // Joining existing organisation
        } elseif (isset($_POST['joinOrganisation'])) {
            $organisationID = $_POST['organisationID'] ?? null;

            if (!$organisationID) {
                $errors[] = "Please select an organisation.";
            }

            if (empty($errors)) {
                try {
                    // Assign the user to the selected organisation
                    $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                    $stmt = $dbConn->prepare($sql);
                    $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);
                    $_SESSION['successMessage'] = "You have successfully joined the organisation.";


                    // Refreshes the page and data from database
                    header("Location: organisationPage.php");
                    exit();
                } catch (Exception $e) {
                    $errors[] = "Error joining organisation: " . $e->getMessage();
                }
            }

            // Leave existing organisation
        } else if (isset($_POST['leaveOrganisation'])) {
            try {
                // Check if user is team leader
                $sql = "SELECT teamLeaderID from organisationTable WHERE organisationID = :currentOrgID";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':currentOrgID' => $currentOrgID]);
                $teamLeaderID = $stmt->fetchColumn();

                if ($teamLeaderID == $userID) {
                    $errors[] = "You cannot leave an organisaiton if you are a team leader";
                } else {
                    $sql = "SELECT COUNT(*) FROM userTable where organisationID = :currentOrgID";
                    $stmt = $dbConn->prepare($sql);
                    $stmt->execute([':currentOrgID' => $currentOrgID]);
                    $userCount = $stmt->fetchColumn();

                    // Delete organisation if user is the team leader
                    if ($teamLeaderID == $userID) {
                        if ($userCount > 0) {
                            $sql = "UPDATE userTable SET organisationID = NULL WHERE organisationID = :currentOrgID";
                            $stmt = $dbConn->prepare($sql);
                            $stmt->execute([':currentOrgID' => $currentOrgID]);

                            $sql = "DELETE FROM organisationTable WHERE organisationID = :currentOrgID";
                            $stmt = $dbConn->prepare($sql);
                            $stmt->execute([':currentOrgID' => $currentOrgID]);

                            $_SESSION['successMessage'] = "You have successfully left and deleted the organisation.";
                            
                        } else {
                            $sql = "UPDATE userTable SET organisationID = NULL WHERE userID = :userID";
                            $stmt = $dbConn->prepare($sql);
                            $stmt->execute(['userID' => $userID]);

                            $_SESSION['successMessage'] = "You have successfully left the organisation.";
                        }
                        $currentOrgName = "You are not part of any organisation. Create or join an organisation.";

                        // Refreshes the page and data from database
                        header("Location: organisationPage.php");
                        exit();
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error leaving organisation: " . $e->getMessage();
            }
        }
    }
    // Clear success messages
    $successMessage = isset($_SESSION['successMessage']) ? $_SESSION['successMessage'] : "";
    unset($_SESSION['successMessage']);
} catch (Exception $e) {
    $errors[] = "Error joining organisation: " . $e->getMessage();
}

// Generate the HTML page structure
echo makePageStart("Manage Organisation - CyberPath", "../../css/stylesheet.css");
echo makeNavMenu("CyberPath");

?>

<div class="container">
    <div class="section">
        <h1 class="title has-text-centered">Manage Organisation</h1>

        <!-- Display success message -->
        <?php if (!empty($successMessage)): ?>
            <div class="notification is-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="notification is-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="columns">

            <!-- Create Organisation Form -->
            <div class="column is-one-third">
                <h2 class="subtitle">Create an Organisation</h2>
                <form method="POST" action="" onsubmit="return confirmCreate();">
                    <div class="field">
                        <label class="label">Organisation Name</label>
                        <div class="control">
                            <input class="input" type="text" name="organisationName" required>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button class="button is-primary" type="submit" name="createOrganisation">Create Organisation</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Join Organisation Form -->
            <div class="column is-one-third">
                <h2 class="subtitle">Join an Existing Organisation</h2>
                <form method="POST" action="" onsubmit="return confirmChange();">
                    <div class="field">
                        <label class="label">Select Organisation</label>
                        <div class="control">
                            <div class="select">
                                <select name="organisationID" required>
                                    <option value=""> Select an Organisation </option>
                                    <?php foreach ($organisations as $organisation): ?>
                                        <option value="<?php echo $organisation['organisationID']; ?>">
                                            <?php echo htmlspecialchars($organisation['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button class="button is-primary" type="submit" name="joinOrganisation">Join Organisation</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Display current organisation -->
            <div class="column is-one-third">
                <h2 class="subtitle">Leave an organisation</h2>
                <?php if ($currentOrgID) : ?>
                    <div class="box current-org">
                        <p class="subtitle">You are currently part of an organisation that is called:<br> <strong><?php echo htmlspecialchars($currentOrgName); ?></strong></p>
                    </div>
                <?php else : ?>
                    <div class="box current-org">
                        <p class="subtitle"><strong><?php echo htmlspecialchars($currentOrgName); ?></strong></p>
                    </div>
                <?php endif; ?>

                <!-- Leave Organisation Form -->
                <form method="POST" action="" onsubmit="return confirmChange();">
                    <div class="field">
                        <div class="control">
                            <button class="button is-danger" type="submit" name="leaveOrganisation">Leave Organisation</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Changing organisation confirm message
    function confirmChange() {
        if (<?php echo $currentOrgID ? 'true' : 'false'; ?>) {
            return confirm("Are you sure you want to leave the current organisation?");
        }
        return true;
    }

    // Creating organisation confirm message
    function confirmCreate() {
        if (<?php echo $isTeamLeader ? 'true' : 'false'; ?>) {
            return confirm("You are already an organisation leader. Do you wish to continue?");
        }
        return true;
    }
</script>

<?php
echo makeFooter();
echo makePageEnd();
?>