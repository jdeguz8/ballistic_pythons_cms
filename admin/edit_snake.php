<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';
checkAdmin();

$snake_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($snake_id <= 0) {
    $_SESSION['error'] = 'Invalid snake ID.';
    header('Location: dashboard.php');
    exit;
}

// Fetch morphs and traits for the form
$morphs = $pdo->query('SELECT * FROM morphs ORDER BY name ASC')->fetchAll();
$traits = $pdo->query('SELECT * FROM traits ORDER BY name ASC')->fetchAll();

// Initialize variables
$errors = [];

// Fetch existing snake data
$stmt = $pdo->prepare('
    SELECT * FROM snakes
    WHERE snake_id = :snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$snake = $stmt->fetch();

if (!$snake) {
    $_SESSION['error'] = 'Snake not found.';
    header('Location: dashboard.php');
    exit;
}

// Fetch existing traits for this snake
$stmt = $pdo->prepare('
    SELECT trait_id FROM snake_traits
    WHERE snake_id = :snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$selected_traits = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch existing images for this snake
$stmt = $pdo->prepare('
    SELECT image_id, image_url FROM snake_images
    WHERE snake_id = :snake_id
');
$stmt->execute(['snake_id' => $snake_id]);
$images = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
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

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update snakes table
            $stmt = $pdo->prepare('
                UPDATE snakes SET
                    name = :name,
                    species = :species,
                    morph_id = :morph_id,
                    gender = :gender,
                    price = :price,
                    availability_status = :availability_status,
                    description = :description
                WHERE snake_id = :snake_id
            ');
            $stmt->execute([
                'name'                => $name,
                'species'             => $species,
                'morph_id'            => $morph_id,
                'gender'              => $gender,
                'price'               => $price,
                'availability_status' => $availability_status,
                'description'         => $description,
                'snake_id'            => $snake_id
            ]);

            // Handle image removals
            if (isset($_POST['remove_images'])) {
                $remove_image_ids = $_POST['remove_images'];

                // Prepare statements
                $stmtGetImage = $pdo->prepare('SELECT image_url FROM snake_images WHERE image_id = :image_id AND snake_id = :snake_id');
                $stmtDeleteImage = $pdo->prepare('DELETE FROM snake_images WHERE image_id = :image_id AND snake_id = :snake_id');

                foreach ($remove_image_ids as $image_id) {
                    // Fetch the image URL
                    $stmtGetImage->execute(['image_id' => $image_id, 'snake_id' => $snake_id]);
                    $image = $stmtGetImage->fetch();

                    if ($image) {
                        // Delete the image file
                        if (file_exists('../' . $image['image_url'])) {
                            unlink('../' . $image['image_url']);
                        }
                        // Delete the image record from the database
                        $stmtDeleteImage->execute(['image_id' => $image_id, 'snake_id' => $snake_id]);
                    }
                }
            }

            // Handle new image uploads
            if (isset($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $uploadedFiles = $_FILES['images'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $upload_dir = '../uploads/snakes/';

                // Create the directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Loop through each uploaded file
                for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
                    $tmp_name = $uploadedFiles['tmp_name'][$i];
                    $file_name = basename($uploadedFiles['name'][$i]);
                    $file_type = mime_content_type($tmp_name);
                    $file_size = $uploadedFiles['size'][$i];

                    // Validate file type and size
                    if (in_array($file_type, $allowed_types)) {
                        if ($file_size <= 2 * 1024 * 1024) { // Limit file size to 2MB
                            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                            $new_filename = uniqid('snake_', true) . '.' . $file_extension;
                            $destination = $upload_dir . $new_filename;

                            if (move_uploaded_file($tmp_name, $destination)) {
                                // Insert image URL into snake_images table
                                $stmtInsertImage = $pdo->prepare('INSERT INTO snake_images (snake_id, image_url) VALUES (:snake_id, :image_url)');
                                $stmtInsertImage->execute([
                                    'snake_id' => $snake_id,
                                    'image_url' => 'uploads/snakes/' . $new_filename
                                ]);
                            } else {
                                $errors[] = 'Failed to upload file: ' . htmlspecialchars($file_name);
                            }
                        } else {
                            $errors[] = 'File too large: ' . htmlspecialchars($file_name);
                        }
                    } else {
                        $errors[] = 'Invalid file type for file: ' . htmlspecialchars($file_name);
                    }
                }
            }

            // Update snake_traits table
            // First, delete existing traits
            $stmt = $pdo->prepare('DELETE FROM snake_traits WHERE snake_id = :snake_id');
            $stmt->execute(['snake_id' => $snake_id]);

            // Then, insert new traits
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

            $_SESSION['success'] = 'Snake updated successfully.';
            header('Location: dashboard.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to update snake: ' . $e->getMessage();
        }
    }
} else {
    // Pre-fill form fields with existing data
    $name = $snake['name'];
    $species = $snake['species'];
    $morph_id = $snake['morph_id'];
    $gender = $snake['gender'];
    $price = $snake['price'];
    $availability_status = $snake['availability_status'];
    $description = $snake['description'];
}

include '../templates/admin_header.php'; ?>

<div class="container mt-5">
    <h2>Edit Snake</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="edit_snake.php?id=<?php echo $snake_id; ?>" method="POST" enctype="multipart/form-data">
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
        <!-- Existing Images -->
        <div class="form-group">
            <label>Existing Images:</label>
            <div class="row">
                <?php foreach ($images as $image): ?>
                    <div class="col-md-3">
                        <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" alt="Snake Image" style="max-width: 100%;">
                        <div class="form-check">
                            <input type="checkbox" name="remove_images[]" value="<?php echo $image['image_id']; ?>" class="form-check-input" id="remove_image_<?php echo $image['image_id']; ?>">
                            <label for="remove_image_<?php echo $image['image_id']; ?>" class="form-check-label">Remove</label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Upload New Images -->
        <div class="form-group">
            <label for="images">Upload New Images:</label>
            <input type="file" class="form-control-file" id="images" name="images[]" multiple>
            <small>Allowed file types: JPEG, PNG, GIF. Max size: 2MB per image.</small>
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
        <button type="submit" class="btn btn-success">Update Snake</button>
    </form>
</div>

<?php include '../templates/admin_footer.php'; ?>
