$(document).ready(function () {
    // Check if the search input exists
    if ($("#search-input").length) {
      var debounceTimer;
  
      $("#search-input").on("input", function () {
        var query = $(this).val();
  
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
          if (query.length > 1) {
            $.ajax({
              url: "search.php", // Remove the leading slash if your site is not at the root
              method: "GET",
              data: { q: query, ajax: 1 },
              dataType: "json",
              success: function (data) {
                var autocompleteList = $("#autocomplete-list");
                autocompleteList.empty();
  
                if (data.length > 0) {
                  data.forEach(function (item) {
                    var safeName = $("<div>").text(item.name).html();
                    var safeSpecies = $("<div>").text(item.species).html();
                    autocompleteList.append(
                      '<a href="snake_details.php?id=' +
                        item.snake_id +
                        '" class="list-group-item list-group-item-action">' +
                        safeName +
                        " (" +
                        safeSpecies +
                        ")</a>"
                    );
                  });
                } else {
                  autocompleteList.append(
                    '<div class="list-group-item">No results found</div>'
                  );
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                console.error("Autocomplete AJAX error:", textStatus, errorThrown);
              },
            });
          } else {
            $("#autocomplete-list").empty();
          }
        }, 300); // Debounce time in milliseconds
      });
  
      // Close the autocomplete list when clicking outside
      $(document).on("click", function (e) {
        if (
          !$(e.target).closest("#search-input").length &&
          !$(e.target).closest("#autocomplete-list").length
        ) {
          $("#autocomplete-list").empty();
        }
      });
    }
  });
  