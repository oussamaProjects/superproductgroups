$(document).ready(function () {
  const $groupPopup = $("#group-popup");
  const $groupProductsContainer = $("#group-products");
  const $productSearchInputNum = $("#group-product-search-num");
  const $productSearchInput = $("#group-product-search");
  const $selectedProductsPopup = $("#selected-products-popup");
  const $selectedProductsList = $("#selected-products-list");
  const $selectedGroupsList = $(".selected-groups-list");

  const $product_prices = $(".js-product-prices");
  const $product_actions = $(".js-product-actions");

  $product_prices.hide();
  $product_actions.hide();

  let selectedProducts = [];
  let groupedProducts = [];

  // Open side popup and populate products
  $(".js-open-group-popup").on("click", function (e) {
    e.preventDefault();
    const products = $(this).data("products");
    const id_group = $(this).data("id_group");
    const name_group = $(this).data("name_group");
    console.log("products", products);
    console.log("name_group", name_group);
    $(".selected-group-name").html(name_group);

    // Populate the popup with products
    if (products && products.length > 0) {
      const productsHtml = products
        .map(
          (product) =>
            `<div class="custom-product">
              <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
              </div>
              <div class="product-infos" for="product-${product.id}">

                <div class="product-actions-container">
                  <div class="product-actions">
                    <div class="quantity-selector">
                      <button class="btn-quantity minus" data-product-id="${
                        product.id
                      }">-</button>
                      <input type="number" class="quantity-input" id="quantity-${
                        product.id
                      }" value="1" min="1" />
                      <button class="btn-quantity plus" data-product-id="${
                        product.id
                      }">+</button>
                    </div>
                  </div>
                  <input
                      class="product-input"
                      type="checkbox"
                      id="product-${product.id}"
                      value="${product.id}"
                      data-product='${JSON.stringify({
                        ...product,
                        quantity: 1,
                      })}'
                      data-id_group='${id_group}'
                  />
                </div>

                <div class="product-count">1</div>
                <div class="product-label">${product.name}</div>
                <div class="product-price">
                  $${parseFloat(product.price).toFixed(
                    2
                  )} <span>PRIX PUBLIC</span>
                </div>

              </div>

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

  // Update quantity in data-product dynamically
  $groupProductsContainer.on("click", ".btn-quantity", function (e) {
    e.preventDefault();
    const button = $(this);
    const input = button.siblings(".quantity-input");
    const productId = button.data("product-id");
    const currentQuantity = parseInt(input.val()) || 1;

    // Determine if it's a "plus" or "minus" button
    const newQuantity = button.hasClass("plus")
      ? currentQuantity + 1
      : Math.max(currentQuantity - 1, 1); // Ensure quantity is at least 1

    // Update the input field
    input.val(newQuantity);

    // Update the data-product attribute
    const checkbox = $(`#product-${productId}`);
    const productData = JSON.parse(checkbox.attr("data-product"));
    productData.quantity = newQuantity;

    // Update the total price
    const totalPrice = parseFloat(productData.price) * newQuantity;
    const product = checkbox.closest(".custom-product");

    product.find(".product-count").text(newQuantity);
    product
      .find(".product-price")
      .html(`$${totalPrice.toFixed(2)} <span>PRIX PUBLIC</span>`);

    checkbox.attr("data-product", JSON.stringify(productData));
  });

  // Optional: Update data-product and product-price when manually editing input field
  $groupProductsContainer.on("change", ".quantity-input", function () {
    const input = $(this);
    const newQuantity = Math.max(parseInt(input.val()) || 1, 1); // Ensure valid quantity
    input.val(newQuantity); // Correct the value if invalid

    const productId = input.attr("id").split("-")[1];
    const checkbox = $(`#product-${productId}`);
    const productData = JSON.parse(checkbox.attr("data-product"));
    productData.quantity = newQuantity;

    // Update the total price
    const totalPrice = parseFloat(productData.price) * newQuantity;
    const product = $(`#product-${productId}`).closest(".custom-product");
    product.find(".product-count").text(newQuantity);
    product
      .find(".product-price")
      .html(`$${totalPrice.toFixed(2)} <span>PRIX PUBLIC</span>`);

    checkbox.attr("data-product", JSON.stringify(productData));
  });

  // Handle product search
  $productSearchInput.on("input", function () {
    const searchQuery = $(this).val().toLowerCase();
    const $productItems = $groupProductsContainer.find(".product-label");
    console.log("$productItems", $productItems);

    $productItems.each(function () {
      const $item = $(this).closest(".custom-product");
      console.log("$item", $item);

      const productName = $item.find(".product-label").text().toLowerCase();

      // Show/hide based on the search query
      if (productName.includes(searchQuery)) {
        $item.css("display", "flex");
      } else {
        $item.hide();
      }
    });
  });

  // Handle confirmation of selected products
  $(".js-confirm-selection").on("click", function () {
    const newlySelectedProducts = $groupProductsContainer
      .find("input:checked")
      .map(function () {
        return $(this).data("product");
      })
      .get();

    // Add new products without replacing existing ones
    newlySelectedProducts.forEach((product) => {
      if (!selectedProducts.some((p) => p.id === product.id)) {
        selectedProducts.push(product);
      }
    });

    console.log("Selected Products:", selectedProducts); // Replace with your logic

    if (selectedProducts.length > 0) {
      // Group products by their id_group
      groupedProducts = selectedProducts.reduce((acc, product) => {
        if (!acc[product.id_group]) {
          acc[product.id_group] = {
            group_name: product.group_name,
            total_price: 0,
            products: [],
          };
        }
        acc[product.id_group].total_price +=
          parseFloat(product.price) * product.quantity;
        acc[product.id_group].products.push(product);
        return acc;
      }, {});
      // $('.sub-total').css('display', 'flex').text(`$${selectedProducts.reduce((acc, product) => acc + parseFloat(product.price) * product.quantity, 0).toFixed(2)}`);
      $(".total-info").css("display", "flex");
      $(".total").text(
        `$${selectedProducts
          .reduce(
            (acc, product) =>
              acc + parseFloat(product.price) * product.quantity,
            0
          )
          .toFixed(2)}`
      );
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
                        `<div class="product-check">
                            <div class="product-infos">
                              <div class="product-quantity">${
                                product.quantity
                              }</div>
                              <div class="product-label">${product.name}</div>
                              <div class="product-price">$${parseFloat(
                                product.price * product.quantity
                              ).toFixed(2)} </div>
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

    console.log("Selected Groups:", Object.values(groupedProducts)); // Replace with your logic

    if (groupedProducts) {
      const selectedHtml = Object.values(groupedProducts)
        .map(
          (group) =>
            `<div class="group">
                <!-- Group Name -->
                <div class="group-name">${group.group_name}</div>
                <div class="group-products">
                  <!-- Products within the group -->
                  ${group.products
                    .map(
                      (product) =>
                        `<div id="selected-product-${
                          product.id
                        }" class="product" data-product='${JSON.stringify(
                          product
                        )}'>

                          <!-- Product Information -->
                          <div class="product-infos">
                            <div class="product-quantity">${
                              product.quantity
                            }</div>
                            <div class="product-label">${product.name} (Code: ${
                          product.code || "N/A"
                        })</div>
                            <div class="product-price">$${parseFloat(
                              product.price * product.quantity
                            ).toFixed(2)}</div>
                          </div>

                          <!-- Product Actions -->
                          <div class="product-actions">
                            <div class="quantity-selector">
                              <button class="btn-quantity minus" data-product-id="${
                                product.id
                              }">-</button>
                              <input type="number" class="quantity-input" id="quantity-${
                                product.id
                              }" value="${product.quantity}" min="1" />
                              <button class="btn-quantity plus" data-product-id="${
                                product.id
                              }">+</button>
                            </div>
                          </div>

                        </div>`
                    )
                    .join("")}
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

  $selectedProductsPopup.on("click", ".btn-quantity", function (e) {
    e.preventDefault();
    const button = $(this);
    const input = button.siblings(".quantity-input");
    const productId = button.data("product-id");
    const currentQuantity = parseInt(input.val()) || 1;

    // Determine if it's a "plus" or "minus" button
    const newQuantity = button.hasClass("plus")
      ? currentQuantity + 1
      : Math.max(currentQuantity - 1, 1); // Ensure quantity is at least 1

    // Update the input field
    input.val(newQuantity);
    console.log("productId", productId);

    // Find the product in the `selectedProducts` array and update its quantity
    const productIndex = selectedProducts.findIndex((p) => p.id == productId);
    console.log("productIndex", productIndex);

    if (productIndex !== -1) {
      selectedProducts[productIndex].quantity = newQuantity;

      // Optionally, update the total price in the `selectedProducts` array
      selectedProducts[productIndex].totalPrice =
        parseFloat(selectedProducts[productIndex].price) * newQuantity;
    }

    // Update the data-product attribute
    const selectedProduct = $(`#selected-product-${productId}`);

    selectedProduct.find(".product-quantity").text(newQuantity);

    const productData = JSON.parse(selectedProduct.attr("data-product"));
    productData.quantity = newQuantity;

    // Update the total price
    const totalPrice = parseFloat(productData.price) * newQuantity;

    selectedProduct.find(".product-price").html(`$${totalPrice.toFixed(2)}`);

    selectedProduct.attr("data-product", JSON.stringify(productData));
    console.log("Updated selectedProducts:", selectedProducts); // Debugging output
  });

  // Handle confirmation of selected products
  $(".add-to-cart").on("click", function () {
    addToCart();
  });

  $(".order").on("click", function () {
    addToCart(prestashop.urls.pages.order);
  });

  function addToCart(redirectUrl = prestashop.urls.pages.cart) {
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
          qty: product.quantity, // Adjust quantity as needed
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

      window.location.href = redirectUrl;

      // Hide the popup
      $selectedProductsPopup.removeClass("visible");
    };

    // Iterate over selected products and add them to the cart
    selectedProducts.forEach(addToCart);
  }

  // Close selected products popup
  $(".js-close-selected-popup").on("click", function () {
    $selectedProductsPopup.removeClass("visible");
  });

  // Close popup
  $(".js-close-popup").on("click", function () {
    $groupPopup.removeClass("visible");
  });
});
