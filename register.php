<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session.

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// Check if the user is already logged in (if so, redirect to homepage)
if (isset($_SESSION['userID'])) {
    header('Location: index.php');  // Redirect to homepage if already logged in
    exit();
}

// Check if the registration form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // No errors, proceed with user registration
        $dbConn = getConnection();

        // Check if the username or email already exists
        $stmt = $dbConn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email already exists.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $insertStmt = $dbConn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->execute();

            // Redirect to login page after successful registration
            header('Location: login.php');
            exit();
        }
    }
}

?>

<!-- HTML Content for Registration -->
<h1 class="title has-text-centered">Register</h1>

<!-- Show errors if any -->
<?php if (!empty($errors)): ?>
    <div class="notification is-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Registration Form -->
<form action="register.php" method="POST">
    <div class="box">
        <div class="field">
            <label class="label" for="username">Username</label>
            <div class="control">
                <input class="input" type="text" name="username" id="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>
        </div>

        <div class="field">
            <label class="label" for="email">Email</label>
            <div class="control">
                <input class="input" type="email" name="email" id="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            </div>
        </div>

        <div class="field">
            <label class="label" for="password">Password</label>
            <div class="control">
                <input class="input" type="password" name="password" id="password" required>
            </div>
        </div>

        <div class="field">
            <label class="label" for="confirmPassword">Confirm Password</label>
            <div class="control">
                <input class="input" type="password" name="confirmPassword" id="confirmPassword" required>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <button class="button is-primary is-fullwidth" type="submit">Register</button>
            </div>
        </div>
    </div>
</form>

<!-- Link to Login page -->
<p class="has-text-centered">
    Already have an account? <a href="login.php">Login here</a>.
</p>

<!-- Footer and Page End -->
<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
