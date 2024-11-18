$(document).ready(function () {
  const $groupPopup = $("#group-popup");
  const $groupProductsContainer = $("#group-products");
  const $productSearchInputNum = $("#group-product-search-num");
  const $productSearchInput = $("#group-product-search");
  const $selectedProductsPopup = $("#selected-products-popup");
  const $selectedProductsList = $("#selected-products-list");

  let selectedProducts = [];

  // Open side popup and populate products
  $(".js-open-group-popup").on("click", function (e) {
    e.preventDefault();
    const products = $(this).data("products");
    console.log("products", products);

    // Populate the popup with products
    if (products && products.length > 0) {
      const productsHtml = products
        .map(
          (product) =>
            `<div class="custom-check">
                  <div class="custom-image">
                    <img src="${product.image}" alt="${product.name}">
                  </div>
                  <div class="custom-infos" for="product-${product.id}">
                    <div class="custom-label">${product.name}</div>
                    <div class="custom-price">$${parseFloat(
                      product.price
                    ).toFixed(2)}</div>
                  </div>
                  <input
                      class="custom-input"
                      type="checkbox"
                      id="product-${product.id}"
                      value="${product.id}"
                      data-product='${JSON.stringify(product)}'
                  />

              </div>`
        )
        .join("");
      $groupProductsContainer.html(productsHtml);
    } else {
      $groupProductsContainer.html("<p>No products found for this group.</p>");
    }

    // Show the popup
    $selectedProductsPopup.removeClass("visible");
    $groupPopup.addClass("visible");
  });

  // Handle product search
  $productSearchInput.on("input", function () {
    const searchQuery = $(this).val().toLowerCase();
    const $productItems = $groupProductsContainer.find(".custom-label");
    console.log("$productItems", $productItems);

    $productItems.each(function () {
      const $item = $(this).closest(".custom-check");
      console.log("$item", $item);

      const productName = $item.find(".custom-label").text().toLowerCase();

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
    selectedProducts = $groupProductsContainer
      .find("input:checked")
      .map(function () {
        return $(this).data("product");
      })
      .get();

    console.log("Selected Products:", selectedProducts); // Replace with your logic

    // Hide the popup
    $groupPopup.removeClass("visible");
  });

  // Open the selected products popup
  $(".js-view-selected-products").on("click", function (e) {
    e.preventDefault();
    console.log("selectedProducts", selectedProducts);

    if (selectedProducts.length > 0) {
      const selectedHtml = selectedProducts
        .map(
          (product) =>
            `<div class="custom-check">
                  <div class="custom-image">
                    <img src="${product.image}" alt="${product.name}">
                  </div>
                  <div class="custom-infos" for="product-${product.id}">
                    <div class="custom-label">${product.name}</div>
                    <div class="custom-price">$${parseFloat(
                      product.price
                    ).toFixed(2)}</div>
                  </div>
              </div>`
        )
        .join("");
      $selectedProductsList.html(selectedHtml);
    } else {
      $selectedProductsList.html("<p>Aucun produit sélectionné.</p>");
    }

    $groupPopup.removeClass("visible");
    $selectedProductsPopup.addClass("visible");
  });

  // Close selected products popup
  $(".js-close-selected-popup").on("click", function () {
    $selectedProductsPopup.removeClass("visible");
  });

  // Close popup
  $(".js-close-popup").on("click", function () {
    $groupPopup.removeClass("visible");
  });
});
