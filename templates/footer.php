<?php
// templates/footer.php
?>
<div class="container p-4">
    </section>
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <span class="text-muted">&copy; <?php echo date('Y'); ?> Ballistic Pythons</span>
        <section class="mb-4">
      <!-- Facebook -->
      <a class="btn btn-outline-primary btn-floating m-1" href="https://www.facebook.com/BallisticPythons/" role="button" target="_blank">
        <i class="fab fa-facebook-f"></i>
      </a>
      <!-- Instagram -->
      <a class="btn btn-outline-danger btn-floating m-1" href="https://www.instagram.com/ballisticpythons/?hl=en" role="button" target="_blank">
        <i class="fab fa-instagram"></i>
      </a>
      </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/trait-handler.js"></script>
    <script>
  $(document).ready(function() {
    Fancybox.bind('[data-fancybox]', {});
  });
</script>

<!-- Your Custom JS Files -->
<script src="js/autocomplete.js"></script>
<script src="js/viewdetails.js"></script>

<script>
$(document).ready(function () {
    // Event listener for "View Details" button
    $('.view-details-btn').on('click', function (e) {
        e.preventDefault();
        
        // Get the snake ID from the button's data attribute
        const snakeId = $(this).data('snake-id');
        
        // URL to fetch snake details
        const url = 'get_snake_details.php?id=' + snakeId;
        
        // Fetch details via AJAX
        $.ajax({
            url: url,
            method: 'GET',
            beforeSend: function () {
                // Optional: Add a loading spinner or disable the button
                $('#snakeDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
            },
            success: function (response) {
                // Load the response into the modal content
                $('#snakeDetailsContent').html(response);
                
                // Show the modal
                $('#snakeDetailsModal').modal('show');
            },
            error: function (xhr, status, error) {
                // Handle errors (optional)
                $('#snakeDetailsContent').html('<p class="text-danger">Failed to load snake details. Please try again later.</p>');
            }
        });
    });
});
</script>
