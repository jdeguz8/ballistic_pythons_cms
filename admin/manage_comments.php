<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin(); // Ensure the user is an administrator

$results_per_page = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $results_per_page;

// Fetch comments
$stmt = $pdo->prepare('
    SELECT comments.*, users.username, snakes.name AS snake_name
    FROM comments
    JOIN users ON comments.user_id = users.user_id
    JOIN snakes ON comments.snake_id = snakes.snake_id
    ORDER BY comments.date_posted DESC
    LIMIT :limit OFFSET :offset
');
$stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll();

// Get total number of comments for pagination
$count_stmt = $pdo->query('SELECT COUNT(*) FROM comments');
$total_comments = $count_stmt->fetchColumn();
$total_pages = ceil($total_comments / $results_per_page);
?>

<?php include '../templates/admin_header.php'; ?>

<div class="container mt-5">
    <h2>Manage Comments</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Comment ID</th>
                <th>Snake</th>
                <th>User</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Submitted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment['comment_id']; ?></td>
                <td><?php echo htmlspecialchars($comment['snake_name']); ?></td>
                <td><?php echo htmlspecialchars($comment['username']); ?></td>
                <td><?php echo htmlspecialchars($comment['comment_text']); ?></td>
                <td><?php echo ucfirst($comment['status']); ?></td>
                <td><?php echo htmlspecialchars($comment['date_posted']); ?></td>
                <td>
                    <?php if ($comment['status'] !== 'approved'): ?>
                        <a href="approve_comment.php?id=<?php echo $comment['comment_id']; ?>" class="btn btn-success btn-sm">Approve</a>
                    <?php endif; ?>
                    <a href="delete_comment.php?id=<?php echo $comment['comment_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="manage_comments.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        &laquo;
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="manage_comments.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="manage_comments.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                        &raquo;
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php include '../templates/admin_footer.php'; ?>
