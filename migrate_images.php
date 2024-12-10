<?php
// migrate_images.php

require_once 'includes/connect.php';

// Fetch all snakes with an image_url
$stmt = $pdo->query('SELECT snake_id, image_url FROM snakes WHERE image_url IS NOT NULL AND image_url != ""');
$snakes = $stmt->fetchAll();

if ($snakes) {
    // Prepare insert statement for snake_images table
    $insertStmt = $pdo->prepare('INSERT INTO snake_images (snake_id, image_url) VALUES (:snake_id, :image_url)');

    foreach ($snakes as $snake) {
        // Check if the image already exists in snake_images to avoid duplicates
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM snake_images WHERE snake_id = ? AND image_url = ?');
        $checkStmt->execute([$snake['snake_id'], $snake['image_url']]);
        $exists = $checkStmt->fetchColumn();

        if (!$exists) {
            $insertStmt->execute([
                'snake_id' => $snake['snake_id'],
                'image_url' => $snake['image_url']
            ]);
        }
    }

    echo 'Migration complete.';
} else {
    echo 'No images to migrate.';
}
?>
