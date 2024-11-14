<?php
// Include functions file and start session
require_once("functions.php");
session_start();

// Initialize an array to hold errors
$errors = [];

// Fetch all organisations from the database
$dbConn = getConnection();
$sql = "SELECT organisationID, name FROM organisationTable";
$stmt = $dbConn->prepare($sql);
$stmt->execute();
$organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['createOrganisation'])) {
        // Create new organisation
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
                $stmt->execute([
                    ':organisationID' => $organisationID,
                    ':userID' => $userID
                ]);

                // Redirect to the main page after joining/creating organisation
                header("Location: index.php");
                exit();

            } catch (Exception $e) {
                $errors[] = "Error creating organisation: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['joinOrganisation'])) {
        // Join an existing organisation
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
                $stmt->execute([
                    ':organisationID' => $organisationID,
                    ':userID' => $userID
                ]);

                // Redirect to the main page after joining
                header("Location: index.php");
                exit();

            } catch (Exception $e) {
                $errors[] = "Error joining organisation: " . $e->getMessage();
            }
        }
    }
}

echo makePageStart("Manage Organisation - CyberPath");
echo makeNavMenu("CyberPath");
?>

<div class="container">
    <div class="section">
        <h1 class="title">Manage Organisation</h1>

        <!-- Display any errors -->
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
                <form method="POST" action="organisation.php">
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
                <form method="POST" action="organisation.php">
                    <div class="field">
                        <label class="label">Select Organisation</label>
                        <div class="control">
                            <div class="select">
                                <select name="organisationID" required>
                                    <option value="">-- Select an Organisation --</option>
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

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
