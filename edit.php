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

$page_options["page"] = "Edit Article";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("Edit Article");

$id = $_GET["id"];

// Fetch the article and newsletter data
$article_query = "SELECT dining_articles.*, dining_newsletters.title AS newsletter_title, dining_newsletters.pdf 
                  FROM dining_articles 
                  LEFT JOIN dining_newsletters ON dining_articles.newsletter_id = dining_newsletters.id 
                  WHERE dining_articles.id = ?";

$article_stmt = $con->prepare($article_query);
if (!$article_stmt) {
    // Handle query preparation error
    die("Error preparing the article query: " . $con->error);
}
$article_stmt->bind_param("i", $id);
$article_stmt->execute();
$article_result = $article_stmt->get_result();
if (!$article_result) {
    // Handle query execution error
    die("Error executing the article query: " . $article_stmt->error);
}
$article = $article_result->fetch_assoc();
$article_stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $content = urldecode($_POST["hiddenContent"]); // Decode the content before saving
    $newsletter_title = $_POST["title"];
    $image_small = $_FILES["image_small"];
    $image_large = $_FILES["image_large"];
    $pdf = $_FILES["pdf"];

    // Process image and PDF uploads
    $image_small_name = processUpload($image_small, false);
    $image_large_name = processUpload($image_large, false);
    $pdf_name = processUpload($pdf, true);

    // Prepare the update query for the article
    $update_article_query =
        "UPDATE dining_articles SET title = ?, `text` = ?, image_small = ?, image_large = ? WHERE id = ?";
    $update_article_stmt = $con->prepare($update_article_query);
    if (!$update_article_stmt) {
        // Handle query preparation error
        die("Error preparing the article update query: " . $con->error);
    }
    $update_article_stmt->bind_param(
        "ssssi",
        $title,
        $content,
        $image_small_name,
        $image_large_name,
        $id
    );
    $update_article_stmt->execute();
    if ($update_article_stmt->error) {
        // Handle query execution error
        die("Error executing the article update query: " . $update_article_stmt->error);
    }
    $update_article_stmt->close();

    // Prepare the update query for the newsletter
    $update_newsletter_query =
        "UPDATE dining_newsletters SET title = ?, pdf = ? WHERE id = ?";
    $update_newsletter_stmt = $con->prepare($update_newsletter_query);
    if (!$update_newsletter_stmt) {
        // Handle query preparation error
        die("Error preparing the newsletter update query: " . $con->error);
    }
    $update_newsletter_stmt->bind_param(
        "ssi",
        $newsletter_title,
        $pdf_name,
        $article["newsletter_id"]
    );
    $update_newsletter_stmt->execute();
    if ($update_newsletter_stmt->error) {
        // Handle query execution error
        die("Error executing the newsletter update query: " . $update_newsletter_stmt->error);
    }
    $update_newsletter_stmt->close();

    // Use JavaScript to redirect to dashboard.php
    echo '<script>window.location.href = "dashboard.php";</script>';
    exit();
}

function processUpload($file, $isPdf = false)
{
    // If no file was uploaded, return false
    if ($file["error"] == UPLOAD_ERR_NO_FILE) {
        return false;
    }

    // Specify your upload directory
    $target_dir = $isPdf ? "../../in-the-news/resources/" : "../resources/";

    // Use basename() function to get the base name of file
    $file_name = basename($file["name"]);

    // Specify the target file and determine upload path
    $target_file = $target_dir . $file_name;

    // Use move_uploaded_file() function to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // File upload succeeded
        return $file_name;
    } else {
        // File upload failed
        die("Error uploading file: " . $file["error"]);
    }
}
?>
<meta charset="UTF-8">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.clipboard.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    var quill = new Quill("#content", {
      theme: "snow"
    });

    // Retrieve the content from the Quill editor and set it in the hidden textarea
    var hiddenContent = document.getElementById("hiddenContent");
    quill.on("text-change", function() {
      var html = quill.root.innerHTML;
      hiddenContent.value = encodeURIComponent(html); // Encode the content before saving
    });
  });
</script>

<style>
  .quill-editor {
    height: 300px; /* Adjust the height as needed */
    border: 1px solid #ccc;
    font-family: Arial, sans-serif;
    font-size: 14px;
  }
</style>

<form method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER[
    "PHP_SELF"
] .
    "?id=" .
    $id; ?>">
  <div class="container">
    <h2 class="mb-4">Edit Article</h2>
    <div class="form-group">
        <label for="title"><strong>Title</strong></label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars(
            $article["title"],
            ENT_QUOTES,
            "UTF-8"
        ); ?>" class="form-control">
    </div>
    <div class="form-group">
        <div id="content" class="quill-editor"><?php echo urldecode($article["text"]); ?></div>
        <textarea id="hiddenContent" name="hiddenContent" style="display: none;"><?php echo urldecode($article["text"]); ?></textarea>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label for="image_small"><strong>Small Image</strong> <small><em>(400 x 400)</em></small></label>
          <input type="file" id="image_small" name="image_small" class="form-control-file">
          <?php if ($article["image_small"]): ?>
            <img src="../resources/<?php echo $article[
                "image_small"
            ]; ?>" width="100">
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          <label for="image_large"><strong>Large Image</strong> <small><em>(600 x 400)</em></small></label>
          <input type="file" id="image_large" name="image_large" class="form-control-file">
          <?php if ($article["image_large"]): ?>
            <img src="../resources/<?php echo $article[
                "image_large"
            ]; ?>" width="100">
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          <label for="pdf"><strong>Upload PDF</strong></label>
          <input type="file" id="pdf" name="pdf" class="form-control-file">
          <?php if ($article["pdf"]): ?>
            <a href="../../in-the-news/resources/<?php echo $article[
                "pdf"
            ]; ?>" target="_blank">View PDF</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="row justify-content-end mt-4">
        <div class="col-md-6 text-left">
            <a href="delete_article.php?id=<?php echo $article["id"]; ?>" class="btn btn-danger">Delete</a>
        </div>
        <div class="col-md-6 text-right">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Publish</button>
        </div>
    </div>
  </div>
</form>

<?php dining_finish(); ?>