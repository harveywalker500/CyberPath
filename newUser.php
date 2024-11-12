<?php
// User details
require_once("functions.php");
$username = 'johnsmith';
$forename = 'John';
$surname = 'Smith';
$email = 'johnsmith@gmail.com';
$password = 'Password1';  // Plain-text password

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Now, save the user details to the database
try {
    $dbConn = getConnection();  // Assuming getConnection() is your DB connection function
    
    // SQL query to insert user data
    $sqlQuery = "INSERT INTO userTable (username, forename, surname, email, password, organisationID) 
                 VALUES (:username, :forename, :surname, :email, :password, :organisationID)";
    
    $stmt = $dbConn->prepare($sqlQuery);
    
    // Bind parameters and execute the statement
    $stmt->execute([
        ':username' => $username,
        ':forename' => $forename,
        ':surname' => $surname,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':organisationID' => NULL  // organisationID is NULL for now
    ]);

    echo "User registered successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>