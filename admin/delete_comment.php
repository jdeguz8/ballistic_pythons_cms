<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete the comment
$stmt = $pdo->prepare('DELETE FROM comments WHERE comment_id = :comment_id');
$stmt->execute(['comment_id' => $comment_id]);

$_SESSION['success'] = 'Comment deleted successfully.';
header('Location: manage_comments.php');
exit;
?>
