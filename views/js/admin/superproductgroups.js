$(document).ready(function () {
  let index = $("#group-rows .form-group").length; // Initial index for new rows

  // Add new row dynamically
  $("#add-row").on("click", function () {
    const prototype = $("#group-rows").data("prototype");
    const newFormRow = $(prototype.replace(/__name__/g, index));
    index++;

    // Append the new row to the group rows container
    $("#group-rows").append(newFormRow);

    // Debug: Ensure the new row and select element exist
    console.log("Added new row:", newFormRow);
    console.log("Select element:", newFormRow.find(".js-product-search"));

    // Reinitialize Select2 for the newly added row
    newFormRow.find(".js-product-search").select2({
        placeholder: "Type to search for products",
        minimumInputLength: 2,
        ajax: {
            url: `${adminBaseUrl}/modules/superproductgroups/ajax-products`, // AJAX endpoint
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // Search term
                    _token: adminToken, // Include CSRF token
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function (product) {
                        return {
                            id: product.id,
                            text: product.text,
                        };
                    }),
                };
            },
        },
        templateResult: function (product) {
            if (!product.id) {
                return product.text;
            }
            return $("<span>" + product.text + "</span>");
        },
        templateSelection: function (product) {
            return product.text || product.id;
        },
        allowClear: true, // Enable clearing the selection
    });
});

  // Event delegation for dynamically added rows
  $("#group-rows").on("click", ".remove-row", function () {
    // Confirm before removing
    if (confirm("Are you sure you want to remove this row?")) {
      $(this).closest(".group-entry").remove();
    }
  });

  // AJAX form submission
  $("#save-form").on("click", function (e) {
    e.preventDefault();
    // Serialize form data
    const actionUrl = $("#group-form").data("action"); // Get the action URL from the data-action attribute on #group-form

    const formData = new FormData();
    // Loop through each input in #group-form and append it to formData
    $("#group-form")
      .find("input, select, textarea")
      .each(function () {
        const input = $(this);
        const name = input.attr("name");
        const value = input.val();

        // If the input is a file, handle it differently
        if (input.attr("type") === "file" && input[0].files.length > 0) {
          formData.append(name, input[0].files[0]);
        } else {
          formData.append(name, value);
        }
      });

    $.ajax({
      url: actionUrl, // Use the form's action attribute
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        // Handle success response (e.g., display success message, reset form)
        $("#message-container").html(
          '<div class="alert alert-success">' + response.message + "</div>"
        );
      },
      error: function (xhr) {
        // Handle error response (e.g., display error message)
        const errors = xhr.responseJSON.errors || [
          "An unexpected error occurred",
        ];
        $("#message-container").html(
          '<div class="alert alert-danger">' + errors.join("<br>") + "</div>"
        );
      },
    });
  });

  // Get admin base URL and token
  const adminBaseUrl = $("body").data("base-url");
  const adminToken = $("body").data("token");

  const $searchInput = $(".js-product-search");
  const $selectedProducts = $(".js-selected-products");

  // Initialize Select2 on the search input
  $searchInput.select2({
    placeholder: "Type to search for products",
    minimumInputLength: 2,
    ajax: {
      url: `${adminBaseUrl}/modules/superproductgroups/ajax-products`, // Base URL for your AJAX endpoint
      dataType: "json",
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // Search term
          _token: adminToken, // Include the token as a query parameter
        };
      },
      processResults: function (data) {
        return {
          results: data.results.map(function (product) {
            return {
              id: product.id,
              text: product.text,
            };
          }),
        };
      },
    },
    templateResult: function (product) {
      if (!product.id) {
        return product.text;
      }
      return $("<span>" + product.text + "</span>");
    },
    templateSelection: function (product) {
      return product.text || product.id; // Ensure selected text is displayed
    },
    allowClear: true, // Enable clearing the selection
  });
});
