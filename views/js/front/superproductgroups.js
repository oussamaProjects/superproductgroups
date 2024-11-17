$(document).ready(function () {
  const $groupPopup = $("#group-popup");
  const $groupProductsContainer = $("#group-products");
  const $productSearchInput = $("#group-product-search");

  // Open side popup and populate products
  $(".js-open-group-popup").on("click", function (e) {
    e.preventDefault();
      const products = $(this).data("products");

      // Populate the popup with products
      if (products && products.length > 0) {
          const productsHtml = products
              .map(
                  (product) =>
                      `<div class="custom-check">
                          <input
                              class="custom-check-input"
                              type="checkbox"
                              id="product-${product.id}"
                              value="${product.id}"
                          />
                          <label class="custom-check-label" for="product-${product.id}">
                              ${product.name} - $${parseFloat(product.price).toFixed(2)}
                          </label>
                      </div>`
              )
              .join("");
          $groupProductsContainer.html(productsHtml);
      } else {
          $groupProductsContainer.html("<p>No products found for this group.</p>");
      }

      // Show the popup
      $groupPopup.addClass("visible");
  });

  // Handle product search
  $productSearchInput.on("input", function () {
      const searchQuery = $(this).val().toLowerCase();
      const $productItems = $groupProductsContainer.find(".custom-check");

      $productItems.each(function () {
          const $item = $(this);
          const productName = $item.find("label").text().toLowerCase();

          // Show/hide based on the search query
          if (productName.includes(searchQuery)) {
              $item.show();
          } else {
              $item.hide();
          }
      });
  });

  // Handle confirmation of selected products
  $(".js-confirm-selection").on("click", function () {
      const selectedProducts = $groupProductsContainer
          .find("input:checked")
          .map(function () {
              return $(this).val();
          })
          .get();

      console.log("Selected Products:", selectedProducts); // Replace with your logic

      // Hide the popup
      $groupPopup.removeClass("visible");
  });

  // Close popup
  $(".js-close-popup").on("click", function () {
      $groupPopup.removeClass("visible");
  });
});
