<!-- admin/delete_morph.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$morph_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the morph exists
$stmt = $pdo->prepare('SELECT * FROM morphs WHERE morph_id = :morph_id');
$stmt->execute(['morph_id' => $morph_id]);
$morph = $stmt->fetch();

if (!$morph) {
    $_SESSION['error'] = 'Morph not found.';
    header('Location: manage_morphs.php');
    exit;
}

// Delete the morph
$stmt = $pdo->prepare('DELETE FROM morphs WHERE morph_id = :morph_id');
$stmt->execute(['morph_id' => $morph_id]);

$_SESSION['success'] = 'Morph deleted successfully.';
header('Location: manage_morphs.php');
exit;
