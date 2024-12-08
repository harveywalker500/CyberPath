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

// Check if the user is already part of an organisation
$userID = $_SESSION['userID'];
// Initalise success message
$successMessage = "";
// Initialise error array
$errors = [];

// Connect to database and fetch all organisations from the database
try {
    $dbConn = getConnection();
    $sql = "SELECT organisationID, name FROM organisationTable";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    }
} catch (Exception $e) {
    $errors[] = "Error fetching data: " . $e->getMessage();
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

                $successMessage = "Organisation created and you have been assigned to it successfully!";

                $sql = "SELECT organisationID, name FROM organisationTable";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute();
                $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':organisationID' => $organisationID, ':userID' => $userID]);

                // Refetch data
                $organisations = fetchOrgs($dbConn);
                $currentOrgName = getCurrentOrgs($dbConn, $organisationID);

                $successMessage = "You have successfully joined  " . htmlspecialchars($currentOrgName) . ".";
            } catch (Exception $e) {
                $errors[] = "Error joining organisation: " . $e->getMessage();
            }
        }
    }
}

function fetchOrgs($dbConn) {
    $sql = "SELECT organisationID, name FROM organisationTable";
    $stmt = $dbConn->prepare($sql);
    $stmt-> execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCurrentOrgs($dbConn, $organisationID) {
    $sql = "SELECT name FROM organisationTable where organisationID = :organisationID";
    $stmt = $dbConn->prepare($sql);
    $stmt-> execute(['organisationID' => $organisationID]);
    return $stmt->fetchColumn();
}

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
            <div class="column is-half">
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

                    <!-- Display current organisation -->
                    <?php if ($currentOrgID) : ?>
                        <div class="column is is-centered">
                            <div class="column has-text-centered">
                                <p class="subtitle">You are currently part of organisation: <strong><?php echo htmlspecialchars($currentOrgName); ?></strong></p>
                            </div>
                        </div>

                    <?php endif; ?>
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