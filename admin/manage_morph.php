<!-- admin/manage_morphs.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch all morphs
$stmt = $pdo->query('SELECT * FROM morphs ORDER BY name ASC');
$morphs = $stmt->fetchAll();

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Manage Morphs</h2>
    <a href="add_morph.php" class="btn btn-success mb-3">Add New Morph</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($morphs as $morph): ?>
                <tr>
                    <td><?php echo htmlspecialchars($morph['name']); ?></td>
                    <td>
                        <a href="edit_morph.php?id=<?php echo $morph['morph_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_morph.php?id=<?php echo $morph['morph_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this morph?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../templates/admin_footer.php'; ?>
