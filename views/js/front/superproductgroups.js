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

  // Handle confirmation of selected products
  $(".js-add-confirmed-selection-to-cart").on("click", function () {
    if (!selectedProducts || selectedProducts.length === 0) {
      console.warn("No products selected to add to the cart.");
      return;
    }

    const totalProducts = selectedProducts.length;
    let addedProducts = 0;

    const addToCart = (product) => {
      $.ajax({
        url: prestashop.urls.pages.cart, // Use PrestaShop's built-in cart URL
        type: "POST",
        data: {
          ajax: 1,
          action: "update",
          add: 1,
          id_product: product.id,
          id_customization: 0, // If customization is not required
          id_product_attribute: 0, // If no specific attribute is selected
          qty: 1, // Adjust quantity as needed
        },
        success: function () {
          console.log(`Product ${product.name} added to cart successfully.`);
          addedProducts++;

          // Check if all products are added
          if (addedProducts === totalProducts) {
            showCompletionEffect();
          }
        },
        error: function (xhr) {
          console.error(
            `Error adding product ${product.name} to cart:`,
            xhr.responseText
          );
        },
      });
    };

    const showCompletionEffect = () => {
      // Add a success message
      const $successMessage = $("<div>", {
        class: "cart-success-message",
        text: "All selected products have been added to the cart!",
      }).appendTo("body");

      // Style the success message
      $successMessage.css({
        position: "fixed",
        top: "20px",
        right: "20px",
        background: "#28a745",
        color: "#fff",
        padding: "10px 20px",
        borderRadius: "5px",
        zIndex: 1000,
        display: "none",
      });

      // Show the success message with fade-in effect
      $successMessage
        .fadeIn(300)
        .delay(1500)
        .fadeOut(300, function () {
          $(this).remove(); // Remove the message after fading out
        });


        window.location.reload();

      // Hide the popup
      $selectedProductsPopup.removeClass("visible");
    };

    // Iterate over selected products and add them to the cart
    selectedProducts.forEach(addToCart);
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
