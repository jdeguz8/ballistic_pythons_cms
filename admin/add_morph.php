<!-- admin/add_morph.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    // Check for duplicate morph name
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM morphs WHERE name = :name');
    $stmt->execute(['name' => $name]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Morph with this name already exists.';
    }

    if (empty($errors)) {
        // Insert morph into the database
        $stmt = $pdo->prepare('INSERT INTO morphs (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        $_SESSION['success'] = 'Morph added successfully.';
        header('Location: manage_morphs.php');
        exit;
    }
}

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Add New Morph</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="add_morph.php" method="POST">
        <div class="form-group">
            <label for="name">Morph Name:</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-success">Add Morph</button>
    </form>
</div>

<?php include '../templates/admin_footer.php'; ?>
