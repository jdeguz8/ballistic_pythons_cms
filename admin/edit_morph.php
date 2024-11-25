<!-- admin/edit_morph.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$morph_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the morph
$stmt = $pdo->prepare('SELECT * FROM morphs WHERE morph_id = :morph_id');
$stmt->execute(['morph_id' => $morph_id]);
$morph = $stmt->fetch();

if (!$morph) {
    $_SESSION['error'] = 'Morph not found.';
    header('Location: manage_morphs.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    // Check for duplicate morph name
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM morphs WHERE name = :name AND morph_id != :morph_id');
    $stmt->execute(['name' => $name, 'morph_id' => $morph_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Another morph with this name already exists.';
    }

    if (empty($errors)) {
        // Update the morph
        $stmt = $pdo->prepare('UPDATE morphs SET name = :name WHERE morph_id = :morph_id');
        $stmt->execute(['name' => $name, 'morph_id' => $morph_id]);

        $_SESSION['success'] = 'Morph updated successfully.';
        header('Location: manage_morphs.php');
        exit;
    }
}

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Edit Morph</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="edit_morph.php?id=<?php echo $morph_id; ?>" method="POST">
        <div class="form-group">
            <label for="name">Morph Name:</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($morph['name']); ?>">
        </div>
        <button type="submit" class="btn btn-success">Update Morph</button>
    </form>
</div>

<?php include '../templates/admin_footer.php'; ?>
