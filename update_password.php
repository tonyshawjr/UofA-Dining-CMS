<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    // User is not logged in. Redirect them to the login page
    header("Location: index.php");
    exit();
}

require_once "../../template/dining-top.inc";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["new_password"];
    $user_id = $_SESSION["id"];

    // Hash the new password using bcrypt
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $con = new db_mysqli("marketing");
    $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    $stmt->execute();

    // Check if the password update was successful
    if ($stmt->affected_rows === 1) {
        // Password updated successfully
        header("Location: dashboard.php");
        exit();
    } else {
        // Password update failed
        $error_message = "Failed to update password. Please try again.";
    }

    $stmt->close();
    $con->close();
}

$page_options["page"] = "Update Password";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("Update Password");
?>

<h2>Update Password</h2>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <div class="container">
        <div class="form-group">
            <label for="new_password"><b>New Password</b></label>
            <input type="password" placeholder="Enter New Password" name="new_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
    </div>
</form>

<?php dining_finish();
?>