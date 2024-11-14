<?php
// Include functions file and start session
require_once("functions.php");
session_start();

// Initialize an array to hold errors
$errors = [];

// Initialize form variables to empty strings
$username = $password = $confirmPassword = $forename = $surname = $email = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the form data
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $forename = trim($_POST['forename'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validation checks
    if (empty($username) || empty($password) || empty($confirmPassword) || empty($forename) || empty($surname) || empty($email)) {
        $errors[] = "Please fill in all fields.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
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
            // Redirect to login page after successful registration
            header("Location: loginForm.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Error registering user: " . $e->getMessage();
        }
    }
}

echo makePageStart("Register - CyberPath");
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
echo makeFooter("This is the footer");
echo makePageEnd();
?>
