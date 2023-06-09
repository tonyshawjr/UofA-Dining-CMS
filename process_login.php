<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/commontools/includes/mysqli.inc";
$con = new db_mysqli("marketing");

$username = $_POST["username"];
$password = $_POST["password"];

// Prepare a SQL statement to retrieve the user details from the database
$stmt = $con->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $hashed_password = $user["password"];

    // Verify the password using MD5 for backward compatibility
    if (md5($password) === $hashed_password) {
        // Password is correct, start a new session
        session_start();
        $_SESSION["id"] = $user["id"];

        // Check if the user needs to update their password
        if ($user["update_password"] == 1) {
            // Redirect the user to the update password page
            header("Location: update_password.php");
            exit();
        } else {
            // Redirect the user to the dashboard
            header("Location: dashboard.php");
            exit();
        }
    }
}

// Invalid username or password, redirect back to the login page
header("Location: index.php?login_error=1");
exit();

$stmt->close();
$con->close();

?>