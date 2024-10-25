<?php
// index.php

require_once 'includes/connect.php';

// Fetch snakes from the database
$stmt = $pdo->prepare('
  SELECT snakes.*, morphs.name AS morph_name
  FROM snakes
  JOIN morphs ON snakes.morph_id = morphs.morph_id
  WHERE availability_status = "available"
  ORDER BY date_added DESC
');
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
          <img src="<?php echo htmlspecialchars($snake['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snake['name']); ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($snake['name']); ?></h5>
            <p class="card-text">
              <strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?><br>
              <strong>Morph:</strong> <?php echo htmlspecialchars($snake['morph_name']); ?><br>
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

<?php include 'templates/footer.php'; ?>
