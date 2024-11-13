<?php
// index.php

require_once 'includes/connect.php';

// Define how many results you want per page
$results_per_page = 9;

// Find out the number of results stored in database
$stmt = $pdo->prepare('SELECT COUNT(*) FROM snakes WHERE availability_status = "available"');
$stmt->execute();
$number_of_results = $stmt->fetchColumn();

// Determine number of total pages available
$number_of_pages = ceil($number_of_results / $results_per_page);

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Determine the SQL LIMIT starting number
$offset = ($page - 1) * $results_per_page;

// Modify your main query to include LIMIT and OFFSET
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
$snakes = $stmt->fetchAll();

?>

<?php include 'templates/header.php'; ?>

<div class="container mt-5">
  <h1 class="mb-4">Available Snakes</h1>
  <div class="row">
    <?php foreach ($snakes as $snake): ?>
      <div class="col-md-4">
        <div class="card mb-4">
          <img src="<?php echo htmlspecialchars($snake['image_url'] ?: 'assets/images/default_snake.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snake['name']); ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($snake['name']); ?></h5>
            <p class="card-text">
              <strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?><br>
              <strong>Morph:</strong> <?php echo htmlspecialchars($snake['morph_name']); ?><br>
              <strong>Traits:</strong>
              <span class="traits">
                <?php
                // Prepare traits
                $traitNames = !empty($snake['trait_names']) ? explode(', ', $snake['trait_names']) : [];
                $traitIds = !empty($snake['trait_ids']) ? explode(',', $snake['trait_ids']) : [];

                foreach ($traitNames as $index => $trait_name):
                  $trait_id = $traitIds[$index];
                ?>
                  <span class="trait badge badge-info" data-trait-id="<?php echo $trait_id; ?>" style="cursor: pointer;">
                    <?php echo htmlspecialchars($trait_name); ?>
                  </span>
                <?php endforeach; ?>
              </span><br>
              <strong>Gender:</strong> <?php echo htmlspecialchars($snake['gender']); ?><br>
              <strong>Price:</strong> $<?php echo number_format($snake['price'], 2); ?>
            </p>
            <a href="snake_details.php?id=<?php echo $snake['snake_id']; ?>" class="btn btn-primary">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>


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

<?php include 'templates/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="assets/js/trait-handler.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
