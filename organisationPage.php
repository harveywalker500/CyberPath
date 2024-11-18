<!--  Organisation Page to create or join an organisation  -->

<?php
// Include functions file and start session
require_once("functions.php");
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: loginForm.php");
    exit();
}

// Check if the user is already part of an organisation
$userID = $_SESSION['userID'];
// Initalise success message
$successMessage = "";
// Initialise error array
$errors =[];

// Connect to database and fetch all organisations from the database
try {
    $dbConn = getConnection();
    $sqlQuery = "SELECT organisationID, name FROM organisationTable";
    $stmt = $dbConn->prepare($sqlQuery);
    $stmt->execute();
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlQuery = "SELECT organisationID, teamLeaderID FROM organisationTable WHERE teamLeaderID = :userID";
    $stmt = $dbConn->prepare($sqlQuery);
    $stmt->execute([':userID' => $userID]);
    $teamLeaderOrg = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($teamLeaderOrg) {
        // If the user is already a team leader, don't allow them to create a new organisation
        $isTeamLeader = true;
        $currentOrgID = $teamLeaderOrg['organisationID'];
    } else {
        // Check if the user is already part of any other organisation
        $sqlQuery = "SELECT organisationID FROM userTable WHERE userID = :userID";
        $stmt = $dbConn->prepare($sqlQuery);
        $stmt->execute([':userID' => $userID]);
        $currentOrgID = $stmt->fetchColumn();
        $isTeamLeader = false;
    }


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['createOrganisation'])) {
        $organisationName = trim($_POST['organisationName'] ?? '');

        if (empty($organisationName)) {
            $errors[] = "Please provide an organisation name.";
        }

        // If the user is a team leader, they cannot create a new organisation
        if ($isTeamLeader) {
            $errors[] = "You are already a team leader of an existing organisation. You cannot create a new organisation.";
        }

        if (empty($errors)) {
            try {
                // Insert the new organisation into the database
                $sqlQuery = "INSERT INTO organisationTable (name, teamLeaderID) VALUES (:name, :teamLeaderID)";
                $stmt = $dbConn->prepare($sqlQuery);
                // Include the current user as the team leader
                $stmt->execute([':name' => $organisationName, ':teamLeaderID' => $userID]);

                // Get the newly created organisation ID
                $organisationID = $dbConn->lastInsertId();

                // Assign the user to the newly created organisation
                $sqlQuery = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                $stmt = $dbConn->prepare($sqlQuery);
                $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);

                $successMessage = "Organisation created and you have been assigned to it successfully!";

            } catch (Exception $e) {
                $errors[] = "Error creating organisation: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['joinOrganisation'])) {
        $organisationID = $_POST['organisationID'] ?? null;

        if (!$organisationID) {
            $errors[] = "Please select an organisation.";
        }

        if (empty($errors)) {
            try {
                // Assign the user to the selected organisation
                $sqlQuery = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                $stmt = $dbConn->prepare($sqlQuery);
                $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);
                $successMessage = "You have successfully joined the organisation.";
            } catch (Exception $e) {
                $errors[] = "Error joining organisation: " . $e->getMessage();
            }
        } 
    }
}
} catch (Exception $e) {
    $errors[] = "Error fetching data: " . $e->getMessage();
}

echo makePageStart("Manage Organisation - CyberPath", "stylesheet.css");
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
            <div class="column is-half">
                <h2 class="subtitle">Create an Organisation</h2>
                <form method="POST" action="" onsubmit="return confirmCreate();">
                    <div class="field">
                    <option value=""> Create an organisation </option>
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
            <div class="column is-half">
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
        </div>
    </div>
</div>

<script>
    // Changing organisation confirm message
    function confirmChange() {
        if (<?php echo $currentOrgID ? 'true' : 'false'; ?>) {
            return confirm("Joining a new organisation will remove you from your current organisation. Do you wish to continue?");
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
