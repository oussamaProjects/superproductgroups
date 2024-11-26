$(document).ready(function () {
  const $groupPopup = $("#group-popup");
  const $groupProductsContainer = $("#group-products");
  const $productSearchInputNum = $("#group-product-search-num");
  const $productSearchInput = $("#group-product-search");
  const $selectedProductsPopup = $("#selected-products-popup");
  const $selectedProductsList = $("#selected-products-list");
  const $selectedGroupsList = $(".selected-groups-list");

  let selectedProducts = [];

  // Open side popup and populate products
  $(".js-open-group-popup").on("click", function (e) {
    e.preventDefault();
    const products = $(this).data("products");
    const id_group = $(this).data("id_group");
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
                    <div class="custom-price">
                      $${parseFloat(product.price).toFixed(2)}
                    </div>

                    <div class="quantity-selector">
                      <button class="btn-quantity minus" data-product-id="${product.id}">-</button>
                      <input
                        type="number"
                        class="quantity-input"
                        id="quantity-${product.id}"
                        value="1"
                        min="1"
                      />
                      <button class="btn-quantity plus" data-product-id="${product.id}">+</button>
                    </div>

                  </div>
                  <input
                      class="custom-input"
                      type="checkbox"
                      id="product-${product.id}"
                      value="${product.id}"
                      data-product='${JSON.stringify(product)}'
                      data-id_group='${id_group}'
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

  // Handle quantity changes
  $groupProductsContainer.on("click", ".btn-quantity", function (e) {
    e.preventDefault();
    const button = $(this);
    const input = button.siblings(".quantity-input").first(); // Find the corresponding input
    const currentQuantity = parseInt(input.val()) || 1;
    const isPlus = button.hasClass("plus");

    // Update the quantity
    if (isPlus) {
      input.val(currentQuantity + 1);
    } else if (currentQuantity > 1) {
      input.val(currentQuantity - 1);
    }
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

    if (selectedProducts.length > 0) {
      // Group products by their group_id
      const groupedProducts = selectedProducts.reduce((acc, product) => {
        if (!acc[product.group_id]) {
          acc[product.group_id] = {
            group_name: product.group_name,
            total_price: 0,
            products: [],
          };
        }
        acc[product.group_id].total_price += parseFloat(product.price);
        acc[product.group_id].products.push(product);
        return acc;
      }, {});

      // Create HTML for each group
      const groupedHtml = Object.values(groupedProducts)
        .map(
          (group) =>
            `<div class="group">
                  <div class="group-name">${group.group_name}</div>
                  <div class="group-total-price">$${group.total_price.toFixed(
                    2
                  )}</div>
                   <span class="js-view-selected-products">Voir Produits Sélectionnés</span>
                <div class="hidden group-products">
                  ${group.products
                    .map(
                      (product) =>
                        `<div class="custom-check">
                            <div class="custom-infos">
                              <div class="custom-label">${product.name}</div>
                              <div class="custom-price">$${parseFloat(
                                product.price
                              ).toFixed(2)}</div>
                            </div>
                          </div>`
                    )
                    .join("")}
                </div>
            </div>`
        )
        .join("");

      $selectedGroupsList.html(groupedHtml);
    } else {
      $selectedGroupsList.html("<p>Aucun produit sélectionné.</p>");
    }

    // Hide the popup
    $groupPopup.removeClass("visible");
  });

  // Open the selected products popup
  $(document).on("click", ".js-view-selected-products", function (e) {
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
