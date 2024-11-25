<!-- admin/manage_traits.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch all traits
$stmt = $pdo->query('SELECT * FROM traits ORDER BY name ASC');
$traits = $stmt->fetchAll();

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Manage Traits</h2>
    <a href="add_trait.php" class="btn btn-success mb-3">Add New Trait</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($traits as $trait): ?>
                <tr>
                    <td><?php echo htmlspecialchars($trait['name']); ?></td>
                    <td>
                        <a href="edit_trait.php?id=<?php echo $trait['trait_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_trait.php?id=<?php echo $trait['trait_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this trait?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../templates/admin_footer.php'; ?>
