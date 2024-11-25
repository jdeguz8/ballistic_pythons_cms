<!-- admin/add_trait.php -->
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

    // Check for duplicate trait name
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM traits WHERE name = :name');
    $stmt->execute(['name' => $name]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Trait with this name already exists.';
    }

    if (empty($errors)) {
        // Insert trait into the database
        $stmt = $pdo->prepare('INSERT INTO traits (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        $_SESSION['success'] = 'Trait added successfully.';
        header('Location: manage_traits.php');
        exit;
    }
}

include '../templates/admin_header.php';
?>

<div class="container mt-5">
    <h2>Add New Trait</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="add_trait.php" method="POST">
        <div class="form-group">
            <label for="name">Trait Name:</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-success">Add Trait</button>
    </form>
</div>

<?php include '../templates/admin_footer.php'; ?>
