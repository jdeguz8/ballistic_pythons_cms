<?php
require_once 'includes/connect.php';

// Get the morph ID from the URL
$morph_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($morph_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch morph details
$stmt = $pdo->prepare('SELECT * FROM morphs WHERE morph_id = :morph_id');
$stmt->execute(['morph_id' => $morph_id]);
$morph = $stmt->fetch();

if (!$morph) {
    header('Location: index.php');
    exit;
}

// Define pagination variables
$results_per_page = 9;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $results_per_page;

// Fetch snakes associated with the morph
$sql = '
    SELECT snakes.*, morphs.name AS morph_name
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    WHERE snakes.availability_status = "available" AND snakes.morph_id = :morph_id
    ORDER BY snakes.date_added DESC
    LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':morph_id', $morph_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$snakes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of snakes for pagination
$count_stmt = $pdo->prepare('
    SELECT COUNT(*) FROM snakes WHERE availability_status = "available" AND morph_id = :morph_id
');
$count_stmt->execute(['morph_id' => $morph_id]);
$total_snakes = $count_stmt->fetchColumn();
$total_pages = ceil($total_snakes / $results_per_page);

// Fetch images for snakes
$snake_ids = array_column($snakes, 'snake_id');
if (!empty($snake_ids)) {
    $placeholders = implode(',', array_fill(0, count($snake_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT snake_id, image_url
        FROM snake_images
        WHERE snake_id IN ($placeholders)
    ");
    $stmt->execute($snake_ids);
    $all_images = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
} else {
    $all_images = [];
}

// Fetch all morphs for dropdown (breadcrumb)
$morphs_stmt = $pdo->query('SELECT morph_id, name FROM morphs ORDER BY name ASC');
$morphs = $morphs_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'templates/header.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Morph: <?php echo htmlspecialchars($morph['name']); ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item dropdown">
                <a class="dropdown-toggle" href="#" id="breadcrumbMorphsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Morphs
                </a>
                <div class="dropdown-menu" aria-labelledby="breadcrumbMorphsDropdown">
                    <?php foreach ($morphs as $morphItem): ?>
                        <a class="dropdown-item" href="morph.php?id=<?php echo $morphItem['morph_id']; ?>">
                            <?php echo htmlspecialchars($morphItem['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($morph['name']); ?>
            </li>
        </ol>
    </nav>

    <?php if ($snakes): ?>
        <div class="row">
            <?php foreach ($snakes as $snake): 
                // Get images for this snake
                $images = isset($all_images[$snake['snake_id']]) ? $all_images[$snake['snake_id']] : ['assets/images/default_snake.jpg'];
                $thumbnail_url = htmlspecialchars($images[0]);
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo $thumbnail_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snake['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($snake['name']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars($snake['species']); ?> - <?php echo htmlspecialchars($snake['morph_name']); ?>
                            </p>
                            <p class="card-text">$<?php echo number_format($snake['price'], 2); ?></p>
                            <a href="javascript:void(0);" 
                               class="btn btn-primary view-details-btn" 
                               data-snake-id="<?php echo $snake['snake_id']; ?>">
                               View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination controls -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="morph.php?id=<?php echo $morph_id; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">&laquo;</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="morph.php?id=<?php echo $morph_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="morph.php?id=<?php echo $morph_id; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php else: ?>
        <p>No snakes found for this morph.</p>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="snakeDetailsModal" tabindex="-1" role="dialog" aria-labelledby="snakeDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="snakeDetailsModalLabel">Snake Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="snakeDetailsContent" class="text-center">
          <p>Loading...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
