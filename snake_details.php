<?php
// snake_details.php

require_once 'includes/connect.php';
require_once 'templates/header.php';

// Check if snake ID is provided
if (isset($_GET['id'])) {
    $snake_id = intval($_GET['id']);

    // Fetch snake details
    $stmt = $pdo->prepare('
        SELECT snakes.*, morphs.name AS morph_name
        FROM snakes
        JOIN morphs ON snakes.morph_id = morphs.morph_id
        WHERE snake_id = ?
    ');
    $stmt->execute([$snake_id]);
    $snake = $stmt->fetch();

    if ($snake) {
        // Fetch snake images
        $stmt = $pdo->prepare('SELECT image_url FROM snake_images WHERE snake_id = ?');
        $stmt->execute([$snake_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Fetch traits associated with the snake
        $stmt = $pdo->prepare('
            SELECT traits.name
            FROM traits
            JOIN snake_traits ON traits.trait_id = snake_traits.trait_id
            WHERE snake_traits.snake_id = ?
        ');
        $stmt->execute([$snake_id]);
        $traits = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        echo '<div class="container mt-5"><div class="alert alert-danger">Snake not found.</div></div>';
        include 'templates/footer.php';
        exit();
    }
} else {
    echo '<div class="container mt-5"><div class="alert alert-danger">Invalid snake ID.</div></div>';
    include 'templates/footer.php';
    exit();
}
?>
<div class="container mt-5">
    <h2><?php echo htmlspecialchars($snake['name']); ?></h2>
    <!-- Snake Image Carousel -->
    <?php if (!empty($images)): ?>
        <div id="snakeCarousel" class="carousel slide mb-4" data-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $image_url): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" class="d-block w-100" alt="Snake Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Controls -->
            <?php if (count($images) > 1): ?>
                <a class="carousel-control-prev" href="#snakeCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#snakeCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Display a default image if no images are available -->
        <img src="assets/images/default_snake.jpg" class="img-fluid mb-4" alt="Default Snake Image">
    <?php endif; ?>
    <!-- Snake Details -->
    <div class="row">
        <div class="col-md-8">
            <h4>Details</h4>
            <p><strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?></p>
            <p><strong>Morph:</strong> <?php echo htmlspecialchars($snake['morph_name']); ?></p>
            <p><strong>Gender:</strong> <?php echo ucfirst(htmlspecialchars($snake['gender'])); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($snake['price'], 2); ?></p>
            <p><strong>Availability:</strong> <?php echo ucfirst(htmlspecialchars($snake['availability_status'])); ?></p>
            <?php if (!empty($snake['description'])): ?>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($snake['description'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($traits)): ?>
                <p><strong>Traits:</strong>
                    <?php foreach ($traits as $trait): ?>
                        <span class="badge badge-info"><?php echo htmlspecialchars($trait); ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
        <!-- Contact Form or Purchase Button -->
        <div class="col-md-4">
            <!-- Placeholder for contact or purchase functionality -->
            <h4>Interested?</h4>
            <p>Contact us for more information or to purchase this snake.</p>
            <a href="contact.php?snake_id=<?php echo $snake_id; ?>" class="btn btn-primary">Contact Us</a>
        </div>
    </div>
</div>
<?php include 'templates/footer.php'; ?>
