<!-- admin/approve_comment.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($comment_id > 0) {
    $stmt = $pdo->prepare('UPDATE comments SET comment_status = "approved" WHERE comment_id = :comment_id');
    $stmt->execute(['comment_id' => $comment_id]);
}

header('Location: comments.php');
exit;
?>
