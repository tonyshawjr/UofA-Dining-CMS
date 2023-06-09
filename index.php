<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION["id"])) {
    // User is already logged in. Redirect them to the dashboard
    header("Location: dashboard.php");
    exit();
}

require_once "../../template/dining-top.inc";
?>

<?php
$page_options["page"] = "CMS Login";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("CMS Login");
?>

<?php if (isset($_GET["login_error"]) && $_GET["login_error"] == 1): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars("Invalid username or password. Please try again."); ?></div>
<?php endif; ?>

<form action="process_login.php" method="post">
  <div class="container">
  <h2 class="mb-3"><?php echo htmlspecialchars("In the News CMS Login"); ?></h2>
    <div class="form-group">
      <label for="username"><b><?php echo htmlspecialchars("Username"); ?></b></label>
      <input type="text" placeholder="<?php echo htmlspecialchars("Enter Username"); ?>" name="username" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="password"><b><?php echo htmlspecialchars("Password"); ?></b></label>
      <input type="password" placeholder="<?php echo htmlspecialchars("Enter Password"); ?>" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars("Login"); ?></button>
  </div>
</form>

<?php dining_finish(); ?>
