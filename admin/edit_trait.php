<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$trait_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the trait data (name and description)
$stmt = $pdo->prepare('SELECT name, description FROM traits WHERE trait_id = :trait_id');
$stmt->execute(['trait_id' => $trait_id]);
$trait = $stmt->fetch();

if (!$trait) {
    $_SESSION['error'] = 'Trait not found.';
    header('Location: manage_trait.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($description)) {
        $errors[] = 'Description is required.';
    }

    // Check for duplicate trait name
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM traits WHERE name = :name AND trait_id != :trait_id');
    $stmt->execute(['name' => $name, 'trait_id' => $trait_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Another trait with this name already exists.';
    }

    if (empty($errors)) {
        // Update the trait
        $stmt = $pdo->prepare('UPDATE traits SET name = :name, description = :description WHERE trait_id = :trait_id');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'trait_id' => $trait_id
        ]);

        $_SESSION['success'] = 'Trait updated successfully.';
        header('Location: manage_trait.php');
        exit;
    }
}

include '../templates/admin_header.php';
?>


<div class="container mt-5">
    <h2>Edit Trait</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="edit_trait.php?id=<?php echo $trait_id; ?>" method="POST">
        <div class="form-group">
            <label for="name">Trait Name:</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($trait['name']); ?>">
        </div>
        <div class="form-group">
            <label for="description">Trait Description:</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($trait['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Update Trait</button>
    </form>
</div>


<?php include '../templates/admin_footer.php'; ?>
