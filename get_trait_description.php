<?php
require_once 'includes/connect.php';

// Validate and sanitize trait_id
$trait_id = isset($_GET['trait_id']) ? intval($_GET['trait_id']) : 0;

if ($trait_id > 0) {
    // Fetch the description for the selected trait
    $stmt = $pdo->prepare('SELECT name, description FROM traits WHERE trait_id = :trait_id');
    $stmt->execute(['trait_id' => $trait_id]);
    $trait = $stmt->fetch();

    header('Content-Type: application/json');

    if ($trait) {
        echo json_encode([
            'name' => $trait['name'],
            'description' => $trait['description']
        ]);
    } else {
        echo json_encode(['description' => 'Trait description not found.']);
    }
} else {
    // Invalid trait_id
    header('Content-Type: application/json');
    echo json_encode(['description' => 'Invalid trait ID.']);
}
?>
