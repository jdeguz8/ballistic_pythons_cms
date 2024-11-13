<!-- admin/comments.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch comments
$stmt = $pdo->prepare('
    SELECT comments.*, users.username, snakes.name AS snake_name
    FROM comments
    JOIN users ON comments.user_id = users.user_id
    JOIN snakes ON comments.snake_id = snakes.snake_id
    WHERE comments.comment_status = "pending"
    ORDER BY comments.date_posted DESC
');
$stmt->execute();
$comments = $stmt->fetchAll();

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Pending Comments</h2>
    <?php foreach ($comments as $comment): ?>
        <div class="comment mt-4">
            <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> on <em><?php echo htmlspecialchars($comment['snake_name']); ?></em> at <?php echo date('F j, Y, g:i a', strtotime($comment['date_posted'])); ?></p>
            <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
            <a href="approve_comment.php?id=<?php echo $comment['comment_id']; ?>" class="btn btn-success btn-sm">Approve</a>
            <a href="reject_comment.php?id=<?php echo $comment['comment_id']; ?>" class="btn btn-danger btn-sm">Reject</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../templates/admin_footer.php'; ?>
