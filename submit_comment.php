<?php
require_once 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $snake_id = intval($_POST['snake_id']);
    $user_id = intval($_SESSION['user_id'] ?? 0);
    $comment_text = trim($_POST['comment'] ?? '');

    // Validate input
    $errors = [];
    if (empty($user_id)) {
        $errors[] = 'You must be logged in to comment.';
    }
    if (empty($comment_text)) {
        $errors[] = 'Comment cannot be empty.';
    }

    if (empty($errors)) {
        // Insert comment into database with status 'pending'
        $stmt = $pdo->prepare('
            INSERT INTO comments (snake_id, user_id, comment_text, status, created_at)
            VALUES (:snake_id, :user_id, :comment_text, :status, NOW())
        ');
        $stmt->execute([
            'snake_id' => $snake_id,
            'user_id' => $user_id,
            'comment_text' => $comment_text,
            'status' => 'pending',
        ]);

        $_SESSION['success'] = 'Your comment has been submitted and is awaiting approval.';
        header("Location: snake_details.php?id=$snake_id");
        exit;
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: snake_details.php?id=$snake_id");
        exit;
    }
}
?>
