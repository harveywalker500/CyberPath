<?php
// Include functions file and start session
require_once("functions.php");
session_start();

// Initialize an array to hold errors
$errors = [];

// Initialize form variables to empty strings
$username = $password = $confirmPassword = $forename = $surname = $email = "";

// Password validation metrics
$containsUpperCase = false;
$containsLowerCase = false;
$containsSpecialChar = false;
$specialChar = "!@#$%^&*()-_=+[]{};:'\",.<>?/|\\`~";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the form data
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $forename = trim($_POST['forename'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Check for uppercase, lowercase and special character in password
    for ($i = 0; $i < strlen($password); $i++) {
        $char = $password[$i];
        if(ctype_upper($char)) {
            $containsUpperCase = true;
        }
        if(ctype_lower($char)) {
            $containsLowerCase = true;
        }
        if(strpos($specialChar, $char) !== false) {
            $containsSpecialChar = true;
        }
    }

    // Validation checks
    if (empty($username) || empty($password) || empty($confirmPassword) || empty($forename) || empty($surname) || empty($email)) {
        $errors[] = "Please fill in all fields.";
    }

    if ($ctype_alpha($forename)) {
        $errors[] = "Forename cannot contain special characters.";
    }

    if ($ctype_alpha($surname)) {
        $errors[] = "Surname cannot contain special characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!$containsUpperCase) {
        $errors[] = "Password must include at least 1 uppercase letter.";
    }

    if (!$containsLowerCase) {
        $errors[] = "Password must include at least 1 lowercase letter.";
    }

    if(!$containsSpecialChar) {
        $errors[] = "Password must contain at least 1 special character.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // If no errors, proceed with account creation
    if (empty($errors)) {
        try {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $dbConn = getConnection();
            $sql = "INSERT INTO userTable (username, password, forename, surname, email) VALUES (:username, :password, :forename, :surname, :email)";
            $stmt = $dbConn->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':forename' => $forename,
                ':surname' => $surname,
                ':email' => $email
            ]);

            $userID = $dbConn->lastInsertId();

            // Insert the user into userProgressTable with default values
            $sqlUserProgress = "INSERT INTO userProgressTable (userID, storyCompleted, quizCompleted) 
                                VALUES (:userID, FALSE, FALSE)";
            $stmtUserProgress = $dbConn->prepare($sqlUserProgress);
            $stmtUserProgress->execute([
                ':userID' => $userID
            ]);

            // Insert the user into leaderboardTable with default quiz scores of 0
            $sqlLeaderboard = "INSERT INTO leaderboardTable (userID, quiz1Score, quiz2Score, quiz3Score, quiz4Score, quiz5Score, quiz6Score) 
                            VALUES (:userID, 0, 0, 0, 0, 0, 0)";
            $stmtLeaderboard = $dbConn->prepare($sqlLeaderboard);
            $stmtLeaderboard->execute([
                ':userID' => $userID
            ]);
            // Redirect to login page after successful registration
            header("Location: loginForm.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Error registering user: " . $e->getMessage();
        }
    }
}

echo makePageStart("Register | CyberPath", "../../css/stylesheet.css");
echo makeNavMenu("CyberPath");
?>

<div class="container">
    <div class="section">
        <h1 class="title">Register</h1>

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

        <!-- Registration form -->
        <form method="POST" action="register.php">
            <div class="field">
                <label class="label">Username</label>
                <div class="control">
                    <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Forename</label>
                <div class="control">
                    <input class="input" type="text" name="forename" value="<?php echo htmlspecialchars($forename); ?>" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Surname</label>
                <div class="control">
                    <input class="input" type="text" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Email Address</label>
                <div class="control">
                    <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Password</label>
                <div class="control">
                    <input class="input" type="password" name="password" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Confirm Password</label>
                <div class="control">
                    <input class="input" type="password" name="confirm_password" required>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button class="button is-primary" type="submit">Register</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
echo makeFooter();
echo makePageEnd();
?>
