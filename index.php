<?php
// index.php

require_once 'includes/connect.php';
include 'templates/header.php'; // Includes the opening HTML tags and navigation

$results_per_page = 9;

// Get total number of results for pagination
$stmt = $pdo->prepare('SELECT COUNT(*) FROM snakes WHERE availability_status = "available"');
$stmt->execute();
$number_of_results = $stmt->fetchColumn();
$number_of_pages = ceil($number_of_results / $results_per_page);

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $results_per_page;

// Main query with LIMIT and OFFSET
$stmt = $pdo->prepare('
  SELECT snakes.*, 
         morphs.name AS morph_name, 
         GROUP_CONCAT(DISTINCT traits.name ORDER BY traits.name SEPARATOR ", ") AS trait_names,
         GROUP_CONCAT(DISTINCT traits.trait_id ORDER BY traits.name SEPARATOR ",") AS trait_ids
  FROM snakes
  JOIN morphs ON snakes.morph_id = morphs.morph_id
  LEFT JOIN snake_traits ON snakes.snake_id = snake_traits.snake_id
  LEFT JOIN traits ON snake_traits.trait_id = traits.trait_id
  WHERE snakes.availability_status = "available"
  GROUP BY snakes.snake_id
  ORDER BY snakes.date_added DESC
  LIMIT :limit OFFSET :offset
');
$stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$snakes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all images for the displayed snakes
$snake_ids = array_column($snakes, 'snake_id');
if (!empty($snake_ids)) {
    $placeholders = implode(',', array_fill(0, count($snake_ids), '?'));

    $stmt = $pdo->prepare("
        SELECT snake_id, image_url
        FROM snake_images
        WHERE snake_id IN ($placeholders)
    ");
    $stmt->execute($snake_ids);
    $all_images = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
} else {
    $all_images = [];
}
?>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['info_message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['info_message'] . '</div>';
    unset($_SESSION['info_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ballistic Pythons</title>
</head>
<body>
  <main class="container mt-5">
  <h1 class="mb-4 text-center">Ballistic Pythons</h1>
  <div class="row">
    <?php foreach ($snakes as $snake): 
      // Get images for this snake
      $images = isset($all_images[$snake['snake_id']]) ? $all_images[$snake['snake_id']] : ['assets/images/default_snake.jpg'];
      // Use the first image as the thumbnail
      $thumbnail_url = htmlspecialchars($images[0]);
      $traitNames = !empty($snake['trait_names']) ? explode(', ', $snake['trait_names']) : [];
      $traitIds = !empty($snake['trait_ids']) ? explode(',', $snake['trait_ids']) : [];
    ?>
      <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
      <div class="card mb-4 w-100">
        <!-- Thumbnail Image with Lightbox Link -->
        <a href="<?php echo $thumbnail_url; ?>" data-fancybox="gallery-<?php echo $snake['snake_id']; ?>" data-caption="<?php echo htmlspecialchars($snake['name']); ?>">
          <img src="<?php echo $thumbnail_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snake['name']); ?>">
        </a>
        <!-- Hidden Links for Additional Images -->
        <?php for ($i = 1; $i < count($images); $i++): ?>
          <a href="<?php echo htmlspecialchars($images[$i]); ?>" data-fancybox="gallery-<?php echo $snake['snake_id']; ?>" data-caption="<?php echo htmlspecialchars($snake['name']); ?>" style="display: none;"></a>
        <?php endfor; ?>
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?php echo htmlspecialchars($snake['name']); ?></h5>
          <p class="card-text">
            <strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?><br>
            <strong>Morph:</strong> <?php echo htmlspecialchars($snake['morph_name']); ?><br>
            <strong>Gender:</strong><?php echo htmlspecialchars($snake['gender']); ?><br>
            <span class="traits">
                <?php foreach ($traitNames as $index => $trait_name): ?>
                  <span class="trait badge badge-info" data-trait-id="<?php echo $traitIds[$index]; ?>" style="cursor: pointer;">
                    <?php echo htmlspecialchars($trait_name); ?>
                  </span>
                <?php endforeach; ?>
              </span><br>
            <strong>Price:</strong> $<?php echo htmlspecialchars($snake['price']); ?><br>
          </p>
          <div class="mt-auto">
          <a href="#" class="btn btn-primary view-details-btn" data-snake-id="<?php echo $snake['snake_id']; ?>">View Details</a>
          <a href="add_to_cart.php?snake_id=<?php echo $snake['snake_id']; ?>" class="btn btn-primary btn-add-to-cart">🛒</a>
          </div>
        </div>
      </div>        
    </div>
    <?php endforeach; ?>
      <!-- Snake Details Modal -->
  <div class="modal fade" id="snakeDetailsModal" tabindex="-1" aria-labelledby="snakeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Use modal-lg for a larger modal -->
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="snakeDetailsModalLabel">Snake Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Snake details will be loaded here -->
          <div id="snakeDetailsContent">
            <!-- Content loaded via AJAX -->
          </div>
        </div>
      </div>
    </div>
  </div>
  </div> <!-- End of Row -->

  <!-- Pagination -->
  <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
      <!-- Previous Page -->
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
            <span class="sr-only">Previous</span>
          </a>
        </li>
      <?php endif; ?>

      <!-- Page Numbers -->
      <?php for ($i = 1; $i <= $number_of_pages; $i++): ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>

      <!-- Next Page -->
      <?php if ($page < $number_of_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
            <span class="sr-only">Next</span>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>  
</body>
</html>

<!-- Trait Description Modal -->
<div class="modal fade" id="traitModal" tabindex="-1" role="dialog" aria-labelledby="traitModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="traitModalLabel">Trait Description</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#traitModal').modal('hide');">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="trait-text">
        <!-- Trait description will be loaded here -->
      </div>
    </div>
  </div>
</div>

</main> <!-- End of Main Content -->


<?php include 'templates/footer.php'; // Includes the closing HTML tags ?>
