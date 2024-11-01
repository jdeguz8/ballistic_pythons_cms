<!-- admin/dashboard.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch all snakes
$stmt = $pdo->prepare('
    SELECT snakes.*, morphs.name AS morph_name
    FROM snakes
    LEFT JOIN morphs ON snakes.morph_id = morphs.morph_id
    ORDER BY snakes.date_added DESC
');
$stmt->execute();
$snakes = $stmt->fetchAll();
?>
<?php include '../templates/admin_header.php'; ?>

<div class="container mt-5">
    <h2>Manage Snakes</h2>
    <a href="add_snake.php" class="btn btn-success mb-3">Add New Snake</a>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Morph</th>
                <th>Gender</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($snakes as $snake): ?>
            <tr>
                <td><?php echo htmlspecialchars($snake['name']); ?></td>
                <td><?php echo htmlspecialchars($snake['morph_name']); ?></td>
                <td><?php echo htmlspecialchars($snake['gender']); ?></td>
                <td>$<?php echo number_format($snake['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($snake['availability_status']); ?></td>
                <td>
                    <a href="edit_snake.php?id=<?php echo $snake['snake_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="delete_snake.php?id=<?php echo $snake['snake_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this snake?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../templates/admin_footer.php'; ?>
