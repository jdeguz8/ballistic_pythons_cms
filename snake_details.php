<?php
// snake_details.php

require_once 'includes/connect.php';

// Get the snake ID from the URL parameter
$snake_id = intval($_GET['id']); // The ID of the snake should be passed via URL, e.g., snake_details.php?id=1

// Fetch the snake details
$stmt = $pdo->prepare('
    SELECT snakes.name, snakes.species, snakes.gender, snakes.price, snakes.availability_status, snakes.description, snakes.image_url, morphs.name AS morph_name
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    WHERE snake_id = :snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$snake = $stmt->fetch();

if (!$snake) {
    echo "Snake not found!";
    exit;
}

// Fetch the traits for this snake
$stmt = $pdo->prepare('
    SELECT traits.name, traits.trait_id 
    FROM snake_traits 
    JOIN traits ON snake_traits.trait_id = traits.trait_id 
    WHERE snake_traits.snake_id = :snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$traits = $stmt->fetchAll();
?>

<?php include 'templates/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($snake['image_url']); ?>" alt="<?php echo htmlspecialchars($snake['name']); ?>" class="img-fluid">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($snake['name']); ?></h1>
            <p><strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($snake['gender']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($snake['price'], 2); ?></p>
            <p><strong>Availability:</strong> <?php echo htmlspecialchars($snake['availability_status']); ?></p>
            <p><?php echo htmlspecialchars($snake['description']); ?></p>

            <!-- Display the traits dynamically -->
            <div>
                <strong>Traits:</strong>
                <div class="traits">
                    <?php foreach ($traits as $trait): ?>
                        <span class="trait" data-trait-id="<?php echo $trait['trait_id']; ?>">
                            <?php echo htmlspecialchars($trait['name']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Trait Description Modal or Div -->
            <div id="trait-description" class="hidden">
                <p id="trait-text"></p>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<!-- JavaScript to handle trait clicks -->
<script>
document.querySelectorAll('.trait
