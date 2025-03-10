$(document).ready(function () {
  let index = $("#group-rows .group-entry").length; // Initial index for new rows
  console.log("index", index);

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

        if (input.is("select[multiple]")) {
          // Handle multiple selections
          if (value) {
            value.forEach((val) => formData.append(name, val));
          }
        } else if (input.attr("type") === "file" && input[0].files.length > 0) {
          formData.append(name, input[0].files[0]);
        } else {
          formData.append(name, value);
        }
      });

    console.log("formData", formData);

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
        window.location.reload();
      },
      error: function (response) {
        // Handle error response (e.g., display error message)
        $("#message-container").html(
          '<div class="alert alert-danger">' + response.message + "</div>"
        );
        window.location.reload();
      },
    });
  });

  // Get admin base URL and token
  const adminBaseUrl = $("body").data("base-url");
  const adminToken = $("body").data("token");

  const $searchInput = $(".js-product-search");

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

  const $productPopup = $("#product-popup");
  const $productList = $("#product-list");
  let currentGroupId = null;

  // Make the product list sortable
  $productList.sortable({
    placeholder: "sortable-placeholder",
    update: function (event, ui) {
      updateProductOrder();
    },
  });

  const $categoryList = $("#category-list");
  // Make the product list sortable
  $categoryList.sortable({
    placeholder: "sortable-placeholder",
    update: function (event, ui) {
      updateCategoryOrder();
    },
  });

  updateCategoryOrder();

  function updateCategoryOrder() {
    $("#category-list .list-group-item").each(function (index) {
      console.log("index", index);
      const $item = $(this);
      // $item.find(".group-order").text(index + 1); // Update the group order display
      $item.find(".js-group-order").val(index + 1); // Update the product order display
      $item.find(".group-order").text(index + 1); // Update the product order display
      $item.data("group-order", index + 1); // Optionally update an order attribute
    });
  }

  function updateProductOrder() {
    $("#product-list .list-group-item").each(function (index) {
      const $item = $(this);
      $item.find(".product-order").text(index + 1); // Update the product order display
      $item.data("product-order", index + 1); // Optionally update an order attribute
    });
  }

  // Open product popup for the selected group
  $(".js-open-product-popup").on("click", function () {
    currentGroupId = $(this).data("group-id");
    let groupProducts = $(this).data("group-products");

    // Check if it's already an array
    if (Array.isArray(groupProducts)) {
      // Use it directly if it's an array
      console.log("Group Products is an array:", groupProducts);
    } else if (typeof groupProducts === "string" && groupProducts.length > 0) {
      // If it's a non-empty string, split it by commas
      groupProducts = groupProducts.split(",");
      console.log(
        "Group Products was a string, converted to array:",
        groupProducts
      );
    } else {
      // Handle empty or invalid cases
      groupProducts = [];
      console.log("Group Products is empty or invalid.");
    }

    console.log("Opening product popup for group ID:", currentGroupId);
    // Fetch the product list via AJAX
    $.ajax({
      url: `${adminBaseUrl}/modules/superproductgroups/ajax-group-products`,
      type: "GET",
      data: {
        group_id: currentGroupId,
        _token: adminToken, // Include CSRF token
      },
      success: function (response) {
        // Show the popup
        $productPopup.modal("show");
        // Populate the popup with products
        if (!response.products || response.products.length === 0) {
          $productList.html("No products found.");
          return;
        }
        console.log("Fetched products:", response.products);
        const productsHtml = response.products
          .map(
            (product, index) => `
						<li class="list-group-item d-flex align-items-center" data-product-order="${
              index + 1
            }" data-id_group="${currentGroupId}" data-id_product="${
              product.id_product
            }">
							<span class="sort-icon me-2" style="cursor: move;margin-right: 8px;">
								<i class="fas fa-sort"></i> <!-- Drag/Sort icon -->
							</span>
							<span class="product-order">${index + 1}</span>
							<span class="product-info">${product.name}</span>
							<button class="btn btn-danger btn-sm ms-auto js-delete-product" data-id_group="${currentGroupId}" data-id_product="${
              product.id_product
            }">Supprimer</button>
						</li>`
          )
          .join("");
        $productList.html(productsHtml);

        console.log("Products HTML:", productsHtml);
      },
    });
  });

  const $addProductInput = $("#add-product-name"); // Input field for product search
  const $addProductButton = $("#add-product"); // Add product button

  let autocompleteResults = []; // Store autocomplete results for selection

  // Autocomplete logic
  $addProductInput.on("input", function () {
    const query = $(this).val().trim();

    if (query.length >= 3) {
      // Fetch matching products via AJAX
      $.ajax({
        url: `${adminBaseUrl}/modules/superproductgroups/ajax-products`,
        type: "GET",
        dataType: "json",
        data: {
          q: query,
          _token: adminToken,
        },
        success: function (data) {
          console.log("Received autocomplete results:", data);

          autocompleteResults = data.results; // Update autocomplete results
          console.log("Autocomplete results:", autocompleteResults);

          const autocompleteHtml = autocompleteResults
            .map(
              (product) =>
                `<li class="dropdown-item" data-product-id="${product.id}" data-product-name="${product.text}">
									${product.text}
									</li>`
            )
            .join("");

          // Display autocomplete dropdown
          $("#autocomplete-dropdown").html(autocompleteHtml).show();
        },
        error: function () {
          console.error("Failed to fetch autocomplete results.");
        },
      });
    } else {
      $("#autocomplete-dropdown").hide(); // Hide dropdown if query is too short
    }
  });

  // Handle product selection from the dropdown
  $(document).on("click", ".dropdown-item", function () {
    const selectedProductId = $(this).data("product-id");
    const selectedProductName = $(this).data("product-name");

    // Set the input value to the selected product name
    $addProductInput.val(selectedProductName);
    $addProductInput.data("selected-product-id", selectedProductId); // Store the selected product ID
    $("#autocomplete-dropdown").hide(); // Hide the dropdown
  });

  // Add selected product to the list
  $addProductButton.on("click", function () {
    const selectedProductId = $addProductInput.data("selected-product-id");
    const selectedProductName = $addProductInput.val().trim();

    if (selectedProductId && selectedProductName) {
      // Check if the product is already in the list
      if (
        $productList.find(`[data-product-id="${selectedProductId}"]`).length > 0
      ) {
        alert("Ce produit est déjà dans la liste.");
        return;
      }

      // Calculate the next product order
      const nextOrder = $productList.children().length + 1;

      // Add product to the list
      const newProductHtml = `
				<li class="list-group-item d-flex align-items-center" data-product-order="0" data-id_group="${currentGroupId}" data-id_product="${selectedProductId}">
					<span class="sort-icon me-2" style="cursor: move;margin-right: 8px;">
						<i class="fas fa-sort"></i> <!-- Drag/Sort icon -->
					</span>
					<span class="product-order">${nextOrder}</span>
					<span class="product-info"> ${selectedProductName}</span>
					<button class="btn btn-danger btn-sm ms-auto js-delete-product" data-id_group="${currentGroupId}" data-id_product="${selectedProductId}">Supprimer</button>
					</li>`;
      $productList.append(newProductHtml);

      // Clear the input field
      $addProductInput.val("").data("selected-product-id", null);
    } else {
      alert("Veuillez sélectionner un produit valide.");
    }
  });

  // Remove product from the list
  $productList.on("click", ".remove-product", function () {
    $(this).closest("li").remove();
  });

  const $saveProductsButton = $("#save-products");

  // Handle Save Products Button Click
  $saveProductsButton.on("click", function () {
    const selectedProducts = $productList
      .find(".list-group-item")
      .map(function () {
        return {
          id: $(this).data("id_product"),
          order: $(this).data("product-order") || 0, // Include order information
        };
      })
      .get();

    if (selectedProducts.length === 0) {
      alert("Aucun produit sélectionné !");
      return;
    }
    console.log("Selected products:", selectedProducts);

    // Example AJAX request to save products to the group
    $.ajax({
      url: `${adminBaseUrl}/modules/superproductgroups/save-group-products?_token=${adminToken}`, // Replace with actual endpoint
      type: "POST",
      data: {
        groupId: currentGroupId, // Pass the group ID (store it when opening the popup)
        // productIds: selectedProducts,
        products: selectedProducts, // Pass the product IDs and their order
        _token: adminToken, // CSRF token for security
      },

      success: function (response) {
        console.log("Products saved successfully:", response);

        alert(response.message);
        $("#product-popup").removeClass("visible"); // Close the popup
        window.location.reload();
      },
      error: function (xhr) {
        alert("Erreur lors de l'enregistrement des produits.");
      },
    });
  });

  $(document).on("click", ".js-delete-product", function (e) {
    e.preventDefault();
    const $button = $(this);
    const productId = $button.data("id_product");
    const groupId = $button.data("id_group");

    // Confirmation dialog
    if (!confirm("Êtes-vous sûr de vouloir supprimer ce produit du groupe ?")) {
      return;
    }
    console.log("productId", productId);

    // AJAX call to delete the product from the group
    $.ajax({
      url: `${adminBaseUrl}/modules/superproductgroups/delete-group-product?_token=${adminToken}`,
      type: "POST",
      data: {
        groupId: groupId,
        productId: productId,
      },
      success: function (response) {
        alert(response.message);
        // Remove the product from the UI
        $button.closest(".list-group-item").remove();
      },
      error: function (xhr) {
        alert("Erreur lors de la suppression du produit.");
      },
    });
  });

  $(document).on("click", ".js-delete-group", function (e) {
    e.preventDefault();
    deleteButton = $(this);
    groupId = deleteButton.data("group-id");
    if (confirm("Are you sure you want to remove this row?")) {
      $.ajax({
        url: `${adminBaseUrl}/modules/superproductgroups/delete-group?_token=${adminToken}`,
        type: "POST",
        data: {
          groupId: groupId, // Group ID to delete
        },
        success: function (response) {
          alert(response.message);
          // Optionally refresh the group list or update the UI
          deleteButton.closest(".group-entry").remove();
        },
        error: function (xhr) {
          alert("Erreur lors de la suppression du groupe.");
        },
      });
    }
  });

  $(document).on("click", ".js-delete-all-products", function () {
    // Confirmation dialog
    if (
      !confirm(
        "Êtes-vous sûr de vouloir supprimer tous les produits de ce groupe ?"
      )
    ) {
      return;
    }
    console.log("currentGroupId", currentGroupId);

    // AJAX call to delete all products in the group
    $.ajax({
      url: `${adminBaseUrl}/modules/superproductgroups/delete-group-products?_token=${adminToken}`,
      type: "POST",
      data: {
        groupId: currentGroupId,
      },
      success: function (response) {
        alert(response.message);
        // Remove all product items from the UI
        $(`.list-group-item[data-id_group="${currentGroupId}"]`).remove();
      },
      error: function (xhr) {
        alert("Erreur lors de la suppression de tous les produits.");
      },
    });
  });

  $(document).on("click", ".js-export-products", function () {
    console.log("Exporting products for group ID:", currentGroupId);

    // AJAX call to fetch group products
    $.ajax({
      url: `${adminBaseUrl}/modules/superproductgroups/export-group-products?_token=${adminToken}`,
      type: "GET",
      data: {
        groupId: currentGroupId,
      },
      success: function (response) {
        // Create a blob for the CSV file
        const blob = new Blob([response], { type: "text/csv;charset=utf-8;" });

        // Generate a download link
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `group_${currentGroupId}_products.csv`);
        document.body.appendChild(link);
        link.click();

        // Clean up
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
      },
      error: function (xhr) {
        alert("Erreur lors de l'exportation des produits.");
      },
    });
  });
});

$(document).ready(function () {
  setTimeout(() => {
    const $tabsContainer = $("#product-tabs .nav-tabs");
    const $contentContainer = $("#product-tabs-content");
    const $groupForm = $("#group-form"); // Clone the form only once

    if (!$tabsContainer.length || !$contentContainer.length) {
      console.warn("Tab container not found.");
      return;
    }

    // Prevent duplicate tab creation
    if ($("#super_product_groups-tab").length) {
      return;
    }

    // Create new tab button
    const newTabButton = $(`
      <li id="super_product_groups-tab-nav" class="nav-item">
        <a href="#super_product_groups-tab" role="tab" data-toggle="tab" class="nav-link" aria-selected="false">
          Super Product Groups
        </a>
      </li>
    `).appendTo($tabsContainer);

    // Create and append tab content area
    const newTabContent = $(`
      <div role="tabpanel" class="form-contenttab tab-pane container-fluid super_product_groups-tab" id="super_product_groups-tab">
        <div class="card">
          <div class="card-body"></div>
        </div>
      </div>
    `).appendTo($contentContainer);

    // Insert cloned group form
    newTabContent.find(".card-body").append($groupForm);

    // Handle tab switching
    $(document).on("click", "#super_product_groups-tab-nav", function (e) {
      e.preventDefault();
      $(".tab-pane").removeClass("show active");
      $("#super_product_groups-tab").addClass("show active");
    });
  }, 500);
});
