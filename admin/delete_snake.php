<!-- admin/delete_snake.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$snake_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($snake_id > 0) {
    try {
        $pdo->beginTransaction();

        // Delete entries from snake_traits
        $stmt = $pdo->prepare('DELETE FROM snake_traits WHERE snake_id = :snake_id');
        $stmt->execute(['snake_id' => $snake_id]);

        // Fetch the snake to get the image URL
        $stmt = $pdo->prepare('SELECT image_url FROM snakes WHERE snake_id = :snake_id');
        $stmt->execute(['snake_id' => $snake_id]);
        $snake = $stmt->fetch();

        if ($snake) {
            // Delete image file if exists
            if ($snake['image_url'] && file_exists('../' . $snake['image_url'])) {
                unlink('../' . $snake['image_url']);
            }

            // Delete the snake record
            $stmt = $pdo->prepare('DELETE FROM snakes WHERE snake_id = :snake_id');
            $stmt->execute(['snake_id' => $snake_id]);

            $pdo->commit();

            $_SESSION['success'] = 'Snake deleted successfully.';
        } else {
            $pdo->rollBack();
            $_SESSION['error'] = 'Snake not found.';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to delete snake: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid snake ID.';
}

header('Location: dashboard.php');
exit;
?>
