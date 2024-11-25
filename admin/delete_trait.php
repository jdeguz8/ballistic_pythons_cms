<!-- admin/delete_trait.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$trait_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the trait exists
$stmt = $pdo->prepare('SELECT * FROM traits WHERE trait_id = :trait_id');
$stmt->execute(['trait_id' => $trait_id]);
$trait = $stmt->fetch();

if (!$trait) {
    $_SESSION['error'] = 'Trait not found.';
    header('Location: manage_traits.php');
    exit;
}

// Delete the trait
$stmt = $pdo->prepare('DELETE FROM traits WHERE trait_id = :trait_id');
$stmt->execute(['trait_id' => $trait_id]);

$_SESSION['success'] = 'Trait deleted successfully.';
header('Location: manage_traits.php');
exit;
