<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    // User is not logged in. Redirect them to the login page
    header("Location: index.php");
    exit();
}

require_once "../../template/dining-top.inc";
require_once $_SERVER["DOCUMENT_ROOT"] . "/commontools/includes/mysqli.inc";
$con = new db_mysqli("marketing");

$page_options["page"] = "Delete Article";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("Delete Article");

$id = $_GET["id"];

// Sanitize
$id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

// Validate
if (filter_var($id, FILTER_VALIDATE_INT) === false) {
    die("Error: The provided ID is not a valid integer.");
}

// Fetch the article data
$article_query = "SELECT * FROM dining_articles WHERE id = $id";
$article_result = $con->query($article_query);
$article = $article_result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Delete the article
    $delete_article_query = "DELETE FROM dining_articles WHERE id = $id";
    $con->query($delete_article_query);

    // Get the newsletter_id of the article
    $newsletter_id = $article["newsletter_id"];

    // Delete the related newsletter
    $delete_newsletter_query = "DELETE FROM dining_newsletters WHERE id = $newsletter_id";
    $con->query($delete_newsletter_query);

    // Redirect using JavaScript
    echo '<script>window.location.href = "dashboard.php";</script>';
    exit();
}
?>
<div class="d-flex justify-content-between align-items-center">
<p><strong>Are you sure you want to delete this post?</strong></p>
    <div>
    <form method="POST">
    <button type="submit" class="btn btn-danger">Yes, delete it</button>
    <a href="dashboard.php" class="btn btn-secondary">No, take me back</a>
</form>
    </div>
  </div>
  <div style="background-color: #f5f5f5; padding: 40px;">
    <h4><strong>Title:</strong> <?php echo $article["title"]; ?></h4><br/>
    <p><?php echo $article["text"]; ?></p>
</div>

<?php dining_finish();
?>