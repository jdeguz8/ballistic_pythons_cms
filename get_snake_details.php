<?php
require_once 'includes/connect.php';

$snake_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($snake_id <= 0) {
    echo '<p class="text-danger">Invalid snake ID.</p>';
    exit;
}

// Fetch snake details
$stmt = $pdo->prepare('
    SELECT snakes.*, 
           morphs.name AS morph_name, 
           GROUP_CONCAT(DISTINCT traits.name ORDER BY traits.name SEPARATOR ", ") AS trait_names,
           GROUP_CONCAT(DISTINCT traits.trait_id ORDER BY traits.name SEPARATOR ",") AS trait_ids
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    LEFT JOIN snake_traits ON snakes.snake_id = snake_traits.snake_id
    LEFT JOIN traits ON snake_traits.trait_id = traits.trait_id
    WHERE snakes.snake_id = :snake_id
    GROUP BY snakes.snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$snake = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$snake) {
    echo '<p class="text-danger">Snake not found.</p>';
    exit;
}

// Fetch images for this snake
$stmt = $pdo->prepare('SELECT image_url FROM snake_images WHERE snake_id = :snake_id');
$stmt->execute(['snake_id' => $snake_id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Begin outputting the content
?>

<!-- Snake Details Content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-md-6">
      <!-- Image Carousel -->
      <?php if (!empty($images)): ?>
      <div id="snakeCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <?php foreach ($images as $index => $image_url): ?>
          <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
            <img src="<?php echo htmlspecialchars($image_url); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($snake['name']); ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#snakeCarousel" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#snakeCarousel" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
      <?php else: ?>
      <img src="assets/images/default_snake.jpg" class="img-fluid" alt="No image available">
      <?php endif; ?>
    </div>
    <div class="col-md-6">
      <!-- Snake Information -->
      <h2 id="snake-name"><?php echo htmlspecialchars($snake['name']); ?></h2>
      <p><strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?></p>
      <p><strong>Morph:</strong> <?php echo htmlspecialchars($snake['morph_name']); ?></p>
      <?php if (!empty($snake['trait_names'])): ?>
      <p><strong>Traits:</strong> <?php echo htmlspecialchars($snake['trait_names']); ?></p>
      <?php endif; ?>
      <p><strong>Price:</strong> $<?php echo htmlspecialchars($snake['price']); ?></p>
      <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($snake['description'])); ?></p>
      <!-- Add more details as needed -->
    </div>
  </div>
</div>
