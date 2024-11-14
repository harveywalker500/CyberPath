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
    $organisationID = $_POST['organisationID'] ?? null;

    if ($organisationID) {
        try {
            // Assuming userID is stored in session after login
            $dbConn = getConnection();
            $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
            $stmt = $dbConn->prepare($sql);
            $stmt->execute([
                ':organisationID' => $organisationID,
                ':userID' => $_SESSION['userID'] // The logged-in user's ID
            ]);
            // Redirect to dashboard or homepage after successful joining
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Error joining organisation: " . $e->getMessage();
        }
    } else {
        $errors[] = "Please select an organisation.";
    }
}

echo makePageStart("Join an Organisation - CyberPath");
echo makeNavMenu("CyberPath");
?>

<div class="container">
    <div class="section">
        <h1 class="title">Join an Organisation</h1>

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

        <!-- Organisation selection form -->
        <form method="POST" action="joinOrganisation.php">
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
                    <button class="button is-primary" type="submit">Join Organisation</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
