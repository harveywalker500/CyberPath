<?php
require_once("functions.php");
session_start(); // Start the session to store session variables

$errors = []; // Initialize an empty array to hold error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form input data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validation checks
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $errors[] = "Please fill in all fields.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // If no errors, proceed with account creation
    if (empty($errors)) {
        try {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $dbConn = getConnection();
            $sql = "INSERT INTO userTable (username, password) VALUES (:username, :password)";
            $stmt = $dbConn->prepare($sql);
            $stmt->execute([':username' => $username, ':password' => $hashedPassword]);

            // Redirect to login page after successful registration
            header("Location: loginForm.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Error registering user: " . $e->getMessage();
        }
    }
}

echo makePageStart("CyberPath - Register");
echo makeNavMenu("Register");

if (!empty($errors)) {
    // Display errors if any
    echo show_errors($errors);
}
?>

<!-- Registration form -->
<div class="container">
    <h1 class="title has-text-centered">Create an Account</h1>
    
    <form action="register.php" method="POST" class="box">
        <div class="field">
            <label class="label">Username</label>
            <div class="control">
                <input class="input" type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
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

        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button type="submit" class="button is-primary">Register</button>
            </div>
        </div>
    </form>
</div>

<?php
echo makeFooter("This is the footer.");
echo makePageEnd();
?>
