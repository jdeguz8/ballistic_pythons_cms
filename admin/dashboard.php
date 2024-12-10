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

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_added';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

$allowed_sort_columns = ['name', 'price', 'date_added'];
$allowed_order = ['asc', 'desc'];



if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'date_added';
}

if (!in_array($order, $allowed_order)) {
    $order = 'desc';
}


$stmt = $pdo->prepare("SELECT * FROM snakes ORDER BY $sort_by $order");
$stmt->execute();
$snakes = $stmt->fetchAll();

?>
<?php include '../templates/admin_header.php'; ?>

<div class="container mt-5">
    <h2>Manage Snakes</h2>
    <a href="add_snake.php" class="btn btn-success mb-3">Add New Snake</a>
    <a href="manage_morph.php" class="btn btn-success mb-3">Manage Morphs</a>
    <a href="manage_trait.php" class="btn btn-success mb-3">Manage Traits</a>
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
    <form method="GET" class="form-inline mb-3">
    <label for="sort_by" class="mr-2">Sort by:</label>
    <select name="sort_by" id="sort_by" class="form-control mr-2">
        <option value="name">Name</option>
        <option value="date_added">Date Added</option>
        <option value="date_updated">Date Updated</option>
        <option value="price">Price</option>
    </select>
    <select name="order" id="order" class="form-control mr-2">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
    </select>
    <button type="submit" class="btn btn-primary">Sort</button>
</form>
</div>
<li class="nav-item">
    <a class="nav-link" href="manage_comments.php">Manage Comments</a>
</li>


<?php include '../templates/admin_footer.php'; ?>
