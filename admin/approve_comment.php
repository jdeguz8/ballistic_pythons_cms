<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Update the comment status to 'approved'
$stmt = $pdo->prepare('UPDATE comments SET status = :status WHERE comment_id = :comment_id');
$stmt->execute([
    'status' => 'approved',
    'comment_id' => $comment_id,
]);

$_SESSION['success'] = 'Comment approved successfully.';
header('Location: manage_comments.php');
exit;
?>
