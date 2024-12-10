// assets/js/trait-handler.js

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.trait').forEach(function(traitElement) {
        traitElement.addEventListener('click', function() {
            const traitId = this.dataset.traitId;

            // Fetch the description via AJAX
            fetch(`get_trait_description.php?trait_id=${encodeURIComponent(traitId)}`)
                .then(response => response.json())
                .then(data => {
                    // Display the description in the modal
                    document.getElementById('trait-text').innerText = data.description;
                    $('#traitModal').modal('show');
                })
                .catch(error => {
                    console.error('Error fetching trait description:', error);
                });
        });
    });
});

