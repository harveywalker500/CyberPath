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
                $stmt->execute([
                    ':organisationID' => $organisationID,
                    ':userID' => $userID
                ]);

                // Optionally, you can echo success or a message
                echo "<div class='notification is-success'>Organisation created successfully!</div>";
            } catch (Exception $e) {
                $errors[] = "Error creating organisation: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['joinOrganisation'])) {
        $organisationID = $_POST['organisationID'] ?? null;

        if (empty($organisationName)) {
            $errors[] = "Please provide an organisation name.";
        }
        
        if (empty($errors)) {
            try {
                // Insert the new organisation into the database
                $sql = "INSERT INTO organisationTable (name) VALUES (:name)";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([':name' => $organisationName]);
        
                // Check if the insert was successful
                if ($stmt->rowCount() > 0) {
                    // Successfully inserted, now assign the user to the new organisation
                    $organisationID = $dbConn->lastInsertId(); // Get the ID of the newly inserted organisation
                    $userID = $_SESSION['userID'];
        
                    $sql = "UPDATE userTable SET organisationID = :organisationID WHERE userID = :userID";
                    $stmt = $dbConn->prepare($sql);
                    $stmt->execute([
                        ':organisationID' => $organisationID,
                        ':userID' => $userID
                    ]);
        
                    echo "<div class='notification is-success'>Organisation created successfully!</div>";
                } else {
                    $errors[] = "Error: Organisation not created.";
                }
            } catch (Exception $e) {
                $errors[] = "Error creating organisation: " . $e->getMessage();
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
                <form method="POST" action="">
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
                <form method="POST" action="">
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
