// js/viewdetails.js
$(document).ready(function() {
    $('.view-details-btn').on('click', function(e) {
      e.preventDefault(); // Prevent default link behavior
      var snakeId = $(this).data('snake-id');
  
      // Show the modal
      $('#snakeDetailsModal').modal('show');
  
      // Set the modal title (optional)
      $('#snakeDetailsModalLabel').text('Loading...');
  
      // Clear previous content
      $('#snakeDetailsContent').html('<p>Loading details...</p>');
  
      // Fetch snake details via AJAX
      $.ajax({
        url: 'get_snake_details.php',
        method: 'GET',
        data: { id: snakeId },
        dataType: 'html',
        success: function(data) {
          // Update modal content
          $('#snakeDetailsContent').html(data);
  
          // Update modal title (optional)
          var snakeName = $(data).find('#snake-name').text();
          $('#snakeDetailsModalLabel').text(snakeName);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#snakeDetailsContent').html('<p class="text-danger">An error occurred while fetching the snake details.</p>');
          console.error('AJAX Error:', textStatus, errorThrown);
        }
      });
    });
  });
  