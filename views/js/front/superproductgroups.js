$(document).ready(function () {
  const $groupModal = $("#group-modal");
  const $groupProductsContainer = $("#group-products");
  const $productSearchInput = $("#group-product-search");

  // Open group modal and fetch products via AJAX
  $(".js-open-group-modal").on("click", function () {
    const groupId = $(this).data("group-id");

    // Fetch group products
    $.ajax({
      url: ajax_url,
      type: "GET",
      data: {
        action: "getGroupProducts",
        group_id: groupId,
      },
      success: function (data) {
        // Populate the modal with products
        const productsHtml = data.products
          .map(
            (product) =>
              `<div>
                  <input type="checkbox" id="product-${product.id_product}" value="${product.id_product}" />
                  <label for="product-${product.id_product}">${product.name}</label>
              </div>`
          )
          .join("");
        $groupProductsContainer.html(productsHtml);

        // Show the modal
        $groupModal.modal("show");
      },
    });
  });

  // Handle product search
  $productSearchInput.on("input", function () {
    const searchQuery = $(this).val();
    const groupId = $(".js-open-group-modal").data("group-id");

    // Fetch filtered group products
    $.ajax({
      url: ajax_url,
      type: "GET",
      data: {
        action: "getGroupProducts",
        group_id: groupId,
        search: searchQuery,
      },
      success: function (data) {
        const productsHtml = data.products
          .map(
            (product) =>
              `<div>
                  <input type="checkbox" id="product-${product.id_product}" value="${product.id_product}" />
                  <label for="product-${product.id_product}">${product.name}</label>
              </div>`
          )
          .join("");
        $groupProductsContainer.html(productsHtml);
      },
    });
  });

  // Handle confirmation
  $(".js-confirm-selection").on("click", function () {
    const selectedProducts = $groupProductsContainer
      .find("input:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    console.log("Selected Products:", selectedProducts); // Handle as needed

    // Hide the modal
    $groupModal.modal("hide");
  });
});
