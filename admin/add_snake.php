<!-- admin/add_snake.php -->
<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch morphs and traits for the form
$morphs = $pdo->query('SELECT * FROM morphs ORDER BY name ASC')->fetchAll();
$traits = $pdo->query('SELECT * FROM traits ORDER BY name ASC')->fetchAll();

$errors = [];
$name = $species = $morph_id = $gender = $price = $availability_status = $description = '';
$selected_traits = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $name                 = trim($_POST['name']);
    $species              = trim($_POST['species']);
    $morph_id             = $_POST['morph_id'];
    $gender               = $_POST['gender'];
    $price                = $_POST['price'];
    $availability_status  = $_POST['availability_status'];
    $description          = trim($_POST['description']);
    $selected_traits      = isset($_POST['traits']) ? $_POST['traits'] : [];

    // Validation...

    // Handle image upload...

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into snakes table
            $stmt = $pdo->prepare('
                INSERT INTO snakes (name, species, morph_id, gender, price, availability_status, description, image_url)
                VALUES (:name, :species, :morph_id, :gender, :price, :availability_status, :description, :image_url)
            ');
            $stmt->execute([
                'name'                => $name,
                'species'             => $species,
                'morph_id'            => $morph_id,
                'gender'              => $gender,
                'price'               => $price,
                'availability_status' => $availability_status,
                'description'         => $description,
                'image_url'           => $image_url
            ]);

            $snake_id = $pdo->lastInsertId();

            // Insert into snake_traits table
            $stmt = $pdo->prepare('INSERT INTO snake_traits (snake_id, trait_id) VALUES (:snake_id, :trait_id)');
            foreach ($selected_traits as $trait_id) {
                $stmt->execute([
                    'snake_id' => $snake_id,
                    'trait_id' => $trait_id
                ]);
            }

            $pdo->commit();

            $_SESSION['success'] = 'Snake added successfully.';
            header('Location: dashboard.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to add snake: ' . $e->getMessage();
        }
    }
}
?>
<?php include '../templates/admin_header.php'; ?>

<div class="container mt-5">
    <h2>Add New Snake</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="add_snake.php" method="POST" enctype="multipart/form-data">
        <!-- Form fields similar to before -->
        <!-- Add traits selection -->
        <div class="form-group">
            <label for="traits">Traits:</label>
            <select name="traits[]" class="form-control" multiple>
                <?php foreach ($traits as $trait): ?>
                    <option value="<?php echo $trait['trait_id']; ?>" <?php echo in_array($trait['trait_id'], $selected_traits) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($trait['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Hold Ctrl (Windows) or Command (Mac) to select multiple traits.</small>
        </div>
        <!-- Rest of the form -->
        <button type="submit" class="btn btn-success">Add Snake</button>
    </form>
</div>

<?php include '../templates/admin_footer.php'; ?>
