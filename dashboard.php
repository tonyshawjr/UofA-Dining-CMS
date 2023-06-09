<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    // User is not logged in. Redirect them to the login page
    header("Location: index.php");
    exit();
}

// Logout logic
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

require_once "../../template/dining-top.inc";
require_once $_SERVER["DOCUMENT_ROOT"] . "/commontools/includes/mysqli.inc";
$con = new db_mysqli("marketing");

$page_options["page"] = "CMS Dashboard";
$page_options["sub-header-image"] = "";
$page_options["sub-header-image-alt"] = "";

dining_start("CMS Dashboard");

// Number of articles per page
$articles_per_page = 10;

// Get the page number from the query string (or default to the first page)
$page_number =
    isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;

// Calculate the offset for the SQL query
$offset = ($page_number - 1) * $articles_per_page;

// Get the total number of articles
$total_articles_query = "SELECT COUNT(*) FROM dining_articles";
$total_articles_result = $con->query($total_articles_query);
$total_articles = $total_articles_result->fetch_array()[0];

// Calculate the total number of pages
$total_pages = ceil($total_articles / $articles_per_page);

// Modify the articles query to include a LIMIT clause
$articles_query = "SELECT * FROM dining_articles ORDER BY date_created DESC LIMIT $offset, $articles_per_page";
$articles_result = $con->query($articles_query);
?>

<div id="content">
  <div class="d-flex justify-content-between align-items-center">
    <h2 class="mb-3">Dining Articles</h2>
    <div>
      <a href="create.php" class="btn btn-primary mr-2">Create New</a>
      <a href="?logout" class="btn btn-dark">Logout</a>
    </div>
  </div>
  <div id="content-list">
    <table class="table">
      <thead>
        <tr>
          <th><strong>Title</strong></th>
          <th><strong>Date Created</strong></th>
          <th></th>
        </tr>
      </thead>
      <tbody id="article-list">
      <?php while ($row = $articles_result->fetch_assoc()): ?>
        <tr class="article-tr">
          <td><a href="/dining/news/article-details?id=<?php echo $row[
              "id"
          ]; ?>"><?php echo $row["title"]; ?></a></td>
          <td><?php echo date(
              "F j, Y",
              strtotime($row["date_created"])
          ); ?></td>
          <td class="text-right">
            <a href="/dining/news/article-details?id=<?php echo $row[
                "id"
            ]; ?>" class="btn btn-link btn-sm">View Article</a>
            <a href="edit.php?id=<?php echo $row[
                "id"
            ]; ?>" class="btn btn-primary btn-sm">Edit</a> 
            <a href="delete.php?id=<?php echo $row[
                "id"
            ]; ?>" class="btn btn-danger btn-sm">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <nav aria-label="Page navigation example">
      <ul class="pagination justify-content-end">
        <li class="page-item <?php echo $page_number <= 1
            ? "disabled"
            : ""; ?>">
          <a class="page-link" href="<?php echo $page_number > 1
              ? "?page=" . ($page_number - 1)
              : "#"; ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?php echo $i == $page_number
              ? "active"
              : ""; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $page_number >= $total_pages
            ? "disabled"
            : ""; ?>">
          <a class="page-link" href="<?php echo $page_number < $total_pages
              ? "?page=" . ($page_number + 1)
              : "#"; ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>
</div>
<script>
$(function() {
  $(document).on('click', '.pagination .page-link', function(e) {
    e.preventDefault();
    var page = $(this).attr('href').split('=')[1];
    $('#content-list').load('?page=' + page + ' #content-list');
    history.pushState(null, null, '?page=' + page);
  });

  $(window).on('popstate', function() {
    $('#content-list').load(location.search + ' #content-list');
  });
});
</script>

<?php dining_finish();
?>