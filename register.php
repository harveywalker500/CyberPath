<?php
// Include the functions file
require_once("functions.php");
session_start(); // Starts the session

echo makePageStart("CyberPath");
echo makeNavMenu("CyberPath");

// If the user is already logged in, redirect to homepage
if (isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$input = ['username' => '', 'email' => '', 'password' => '', 'confirmPassword' => ''];

// If the form is submitted, validate the input and insert into the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the input
    $input['username'] = trim($_POST['username']);
    $input['email'] = trim($_POST['email']);
    $input['password'] = trim($_POST['password']);
    $input['confirmPassword'] = trim($_POST['confirmPassword']);

    // Validate the input fields
    if (empty($input['username'])) {
        $errors[] = "Username is required.";
    }
    if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($input['password'])) {
        $errors[] = "Password is required.";
    }
    if ($input['password'] !== $input['confirmPassword']) {
        $errors[] = "Passwords do not match.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        try {
            $dbConn = getConnection();

            // Check if the username or email already exists
            $sql = "SELECT * FROM userTable WHERE username = :username OR email = :email";
            $stmt = $dbConn->prepare($sql);
            $stmt->execute([':username' => $input['username'], ':email' => $input['email']]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username or email already exists.";
            } else {
                // Hash the password before inserting
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

                // Insert the new user into the database
                $sql = "INSERT INTO userTable (username, email, password) VALUES (:username, :email, :password)";
                $stmt = $dbConn->prepare($sql);
                $stmt->execute([
                    ':username' => $input['username'],
                    ':email' => $input['email'],
                    ':password' => $hashedPassword
                ]);

                // Set session and redirect to login page after successful registration
                $_SESSION['username'] = $input['username'];
                $_SESSION['userID'] = $dbConn->lastInsertId(); // Store user ID in session
                header('Location: loginForm.php');
                exit();
            }
        } catch (Exception $e) {
            $errors[] = "Error registering user: " . $e->getMessage();
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
                <input class="input" type="text" name="username" id="username" required value="<?= htmlspecialchars($input['username']) ?>">
            </div>
        </div>

        <div class="field">
            <label class="label" for="email">Email</label>
            <div class="control">
                <input class="input" type="email" name="email" id="email" required value="<?= htmlspecialchars($input['email']) ?>">
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

<!-- Footer and Page End -->
<?php
echo makeFooter("This is the footer with a registration link.");
echo makePageEnd();
?>
