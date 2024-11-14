<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$morphs = $pdo->query('SELECT * FROM morphs ORDER BY name ASC')->fetchAll();
$traits = $pdo->query('SELECT * FROM traits ORDER BY name ASC')->fetchAll();

$errors = [];

$name = '';
$species = '';
$morph_id = '';
$gender = '';
$price = '';
$availability_status = '';
$description = '';
$selected_traits = [];
$image_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $morph_id = $_POST['morph_id'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $price = $_POST['price'] ?? '';
    $availability_status = $_POST['availability_status'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $selected_traits = $_POST['traits'] ?? [];

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($species)) {
        $errors[] = 'Species is required.';
    }
    if (empty($morph_id)) {
        $errors[] = 'Morph is required.';
    }
    if (empty($gender)) {
        $errors[] = 'Gender is required.';
    }
    if (empty($price) || !is_numeric($price)) {
        $errors[] = 'Valid price is required.';
    }
    if (empty($availability_status)) {
        $errors[] = 'Availability status is required.';
    }

    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($price) || !is_numeric($price)) {
        $errors[] = 'Valid price is required.';
    }
    

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Validate and process the uploaded file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('snake_', true) . '.' . $file_extension;

            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $destination = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_url = 'uploads/' . $new_filename;
            } else {
                $errors[] = 'Failed to move uploaded file.';
                $image_url = '';
            }
        } else {
            $errors[] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
            $image_url = '';
        }
    } else {
        $image_url = ''; // Or set to a default image path
    }

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
            if (!empty($selected_traits)) {
                $stmt = $pdo->prepare('INSERT INTO snake_traits (snake_id, trait_id) VALUES (:snake_id, :trait_id)');
                foreach ($selected_traits as $trait_id) {
                    $stmt->execute([
                        'snake_id' => $snake_id,
                        'trait_id' => $trait_id
                    ]);
                }
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
        <!-- Name -->
        <div class="form-group">
            <label for="name">Name<span class="text-danger">*</span>:</label>
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <!-- Species -->
        <div class="form-group">
            <label for="species">Species<span class="text-danger">*</span>:</label>
            <input type="text" name="species" class="form-control" required value="<?php echo htmlspecialchars($species); ?>">
        </div>
        <!-- Morph -->
        <div class="form-group">
            <label for="morph_id">Morph<span class="text-danger">*</span>:</label>
            <select name="morph_id" class="form-control" required>
                <option value="">Select Morph</option>
                <?php foreach ($morphs as $morph): ?>
                    <option value="<?php echo $morph['morph_id']; ?>" <?php echo ($morph_id == $morph['morph_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($morph['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Gender -->
        <div class="form-group">
            <label for="gender">Gender<span class="text-danger">*</span>:</label>
            <select name="gender" class="form-control" required>
                <option value="">Select Gender</option>
                <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        <!-- Price -->
        <div class="form-group">
            <label for="price">Price<span class="text-danger">*</span>:</label>
            <input type="number" name="price" class="form-control" required value="<?php echo htmlspecialchars($price); ?>" step="0.01">
        </div>
        <!-- Availability Status -->
        <div class="form-group">
            <label for="availability_status">Availability Status<span class="text-danger">*</span>:</label>
            <select name="availability_status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="available" <?php echo ($availability_status == 'available') ? 'selected' : ''; ?>>Available</option>
                <option value="reserved" <?php echo ($availability_status == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                <option value="sold" <?php echo ($availability_status == 'sold') ? 'selected' : ''; ?>>Sold</option>
            </select>
        </div>
        <!-- Description -->
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <!-- Image -->
        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" name="image" class="form-control-file">
        </div>
        <!-- Traits selection -->
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
        <!-- Submit button -->
        <button type="submit" class="btn btn-success">Add Snake</button>
    </form>
</div>
<?php include '../templates/admin_footer.php'; ?>

