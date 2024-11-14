<?php
// snake_details.php

require_once 'includes/connect.php';
session_start(); // Start the session to access $_SESSION variables

// Get the snake ID from the URL parameter
$snake_id = intval($_GET['id']); // The ID of the snake should be passed via URL, e.g., snake_details.php?id=1

// Fetch the snake details
$stmt = $pdo->prepare('
    SELECT snakes.name, snakes.species, snakes.gender, snakes.price, snakes.availability_status, snakes.description, snakes.image_url, morphs.name AS morph_name
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    WHERE snakes.snake_id = :snake_id
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

// Initialize error variable
$error = '';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment_text = trim($_POST['comment_text']);

    if (!empty($comment_text)) {
        $stmt = $pdo->prepare('INSERT INTO comments (snake_id, user_id, comment_text)
        VALUES (:snake_id, :user_id, :comment_text)');

        $stmt->execute([
            'snake_id'       => $snake_id,
            'user_id'        => $_SESSION['user_id'],
            'comment_text'   => $comment_text,
        ]);

        // Redirect to avoid form resubmission
        header('Location: snake_details.php?id=' . $snake_id);
        exit;
    } else {
        $error = 'Comment cannot be empty.';
    }
}

// Fetch comments for this snake
$stmt = $pdo->prepare('
    SELECT comments.*, users.username
    FROM comments
    JOIN users ON comments.user_id = users.user_id
    WHERE comments.snake_id = :snake_id
    ORDER BY comments.date_posted DESC
');
$stmt->execute(['snake_id' => $snake_id]);
$comments = $stmt->fetchAll();


?>
<?php include 'templates/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <?php if ($snake['image_url']): ?>
                <img src="<?php echo htmlspecialchars($snake['image_url']); ?>" alt="<?php echo htmlspecialchars($snake['name']); ?>" class="img-fluid">
            <?php else: ?>
                <img src="path/to/default/image.jpg" alt="Default Image" class="img-fluid">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($snake['name']); ?></h1>
            <p><strong>Species:</strong> <?php echo htmlspecialchars($snake['species']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($snake['gender']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($snake['price'], 2); ?></p>
            <p><strong>Availability:</strong> <?php echo htmlspecialchars($snake['availability_status']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($snake['description'])); ?></p>

            <!-- Display the traits dynamically -->
            <div>
                <strong>Traits:</strong>
                <div class="traits">
                    <?php foreach ($traits as $trait): ?>
                        <span class="trait badge badge-info" data-trait-id="<?php echo $trait['trait_id']; ?>">
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

<!-- Comments Section -->
<div class="container mt-5">
    <h3>Comments</h3>
    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="snake_details.php?id=<?php echo $snake_id; ?>" method="POST">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="comment_text">Add a Comment:</label>
                <textarea name="comment_text" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Comment</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to add a comment.</p>
    <?php endif; ?>

    <!-- Display comments -->
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment mt-4">
                <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> on <?php echo date('F j, Y, g:i a', strtotime($comment['date_posted'])); ?></p>
                <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>

<!-- JavaScript to handle trait clicks -->
<script>
// Your JavaScript code to handle trait clicks
</script>
