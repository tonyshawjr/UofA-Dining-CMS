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

$page_options["page"] = "New Article";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("New Article");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $text = $_POST["hiddenContent"];
    $image_small = $_FILES["image_small"];
    $image_large = $_FILES["image_large"];
    $pdf = $_FILES["pdf"];

    // Process the uploaded files
    $image_small_name = processUpload($image_small);
    $image_large_name = processUpload($image_large);
    $pdf_name = processUpload($pdf, true);

    // Get the current date and time
    $date_created = date("Y-m-d H:i:s");

    // Insert the newsletter into the dining_newsletters table
    $insert_newsletter_query =
        "INSERT INTO dining_newsletters (title, pdf, date_created) VALUES (?, ?, ?)";
    $insert_newsletter_stmt = $con->prepare($insert_newsletter_query);
    if (!$insert_newsletter_stmt) {
        die("Error: " . $con->error);
    }
    $insert_newsletter_stmt->bind_param(
        "sss",
        $title,
        $pdf_name,
        $date_created
    );
    $insert_newsletter_result = $insert_newsletter_stmt->execute();
    if (!$insert_newsletter_result) {
        die("Error: " . $insert_newsletter_stmt->error);
    }
    $newsletter_id = $insert_newsletter_stmt->insert_id;

    // Insert the article into the dining_articles table
    $insert_article_query =
        "INSERT INTO dining_articles (newsletter_id, title, `text`, image_small, image_large, `timestamp`, date_created) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_article_stmt = $con->prepare($insert_article_query);
    if (!$insert_article_stmt) {
        die("Error: " . $con->error);
    }
    $insert_article_stmt->bind_param(
        "issssss",
        $newsletter_id,
        $title,
        $text,
        $image_small_name,
        $image_large_name,
        $date_created,
        $date_created
    );
    $insert_article_result = $insert_article_stmt->execute();
    if (!$insert_article_result) {
        die("Error: " . $insert_article_stmt->error);
    }
    $article_id = $insert_article_stmt->insert_id;

    // Close the prepared statements
    $insert_article_stmt->close();
    $insert_newsletter_stmt->close();

    // Redirect to edit_article.php for the newly created article
    echo '<script>window.location.href = "edit.php?id=' .
        $article_id .
        '";</script>';
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

    // Generate a unique filename for the uploaded file
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $file_name = uniqid() . "." . $file_extension;

    // Specify the target file and determine the upload path
    $target_file = $target_dir . $file_name;

    // Use move_uploaded_file() function to upload the file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // File upload succeeded
        return $file_name;
    } else {
        // File upload failed
        return false;
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

<form method="POST" enctype="multipart/form-data">
    <div class="container">
        <h2 class="mb-4">New Article</h2>
        <div class="form-group">
            <label for="title"><strong>Title</strong></label>
            <input type="text" id="title" name="title" class="form-control">
        </div>
        <div class="form-group">
            <label for="content"><strong>Content</strong></label>
            <div id="content" class="quill-editor"></div>
            <textarea id="hiddenContent" name="hiddenContent" style="display: none;"></textarea>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="image_small"><strong>Small Image</strong> <small><em>(400 x 400)</em></small></label>
                    <input type="file" id="image_small" name="image_small" class="form-control-file">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="image_large"><strong>Large Image</strong> <small><em>(600 x 400)</em></small></label>
                    <input type="file" id="image_large" name="image_large" class="form-control-file">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="pdf"><strong>Upload PDF</strong></label>
                    <input type="file" id="pdf" name="pdf" class="form-control-file">
                </div>
            </div>
        </div>
        <div class="text-right mt-4">
            <a href="dashboard.php" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Publish</button>
        </div>
    </div>
</form>

<?php dining_finish(); ?>
